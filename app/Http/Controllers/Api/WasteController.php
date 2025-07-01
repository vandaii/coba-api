<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\WasteResource;
use App\Models\Waste;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Waste::with(['items', 'storeLocation']);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('doc_number', 'like', "%{$search}%")
                        ->orWhere('remark', 'like', "%{$search}%")
                        ->orWhereHas('storeLocation', function ($q2) use ($search) {
                            $q2->where('store_name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $statuses = explode(',', $request->status);
                $query->whereIn('status', $statuses);
            }

            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Sort by latest by default
            $materialRequests = $query->latest()->paginate(10);

            return response()->json([
                'message' => 'Material request retrieved successfully',
                'data' => WasteResource::collection($materialRequests->load('storeLocation')),
                'meta' => [
                    'current_page' => $materialRequests->currentPage(),
                    'last_page' => $materialRequests->lastPage(),
                    'total_records' => $materialRequests->total(),
                    'per_page' => $materialRequests->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve stock opname',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doc_number' => 'required',
            'waste_date' => 'required|date',
            'prepared_by' => 'required',
            'waste_proof' => 'nullable|array|max:5',
            'waste_proof.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:5012',
            'remark' => 'nullable',
            'items.*.item_code' => 'required',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.UoM' => 'required',
            'items.*.notes' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $storeLocation = $user->store_location_id;

            $wasteProofPaths = [];
            if ($request->hasFile('waste_proof')) {
                foreach ($request->file('waste_proof') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('waste_proofs', $filename, 'public');
                    $wasteProofPaths[] = $path;
                }
            }

            $waste = Waste::create([
                'doc_number' => $request->doc_number,
                'waste_date' => $request->waste_date,
                'prepared_by' => Auth::check() && Auth::user() ? Auth::user()->name : null,
                'store_location' => $storeLocation,
                'photo' => $wasteProofPaths,
                'remark' => $request->remark
            ]);
            foreach ($request->items as $item) {
                $waste->items()->create([
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'UoM' => $item['UoM'],
                    'notes' => $item['notes'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Waste successfully added',
                'data' => new WasteResource($waste)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create waste',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $waste = Waste::with('items', 'storeLocation')->findOrFail($id);
        return new WasteResource($waste);
    }

    public function approveAreaManager(Request $request, $id)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Area Manager') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only area manager can approve.',
                ], 403);
            }

            $waste = Waste::findOrFail($id);
            $waste->update([
                'status' => 'Approved Area Manager',
                'approve_area_manager' => true
            ]);

            return response()->json([
                'message' => 'Waste has approved by Area Manager',
                'data' => new WasteResource($waste)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveAccounting(Request $request, $id)
    {
        try {
            $user = $request->user();
            if ($user->role !== 'Accounting') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only accounting can approve.'
                ]);
            }

            $waste = Waste::findOrFail($id);
            if (!$waste->approve_area_manager === true) {
                return response()->json([
                    'status' => false,
                    'message' => 'Waste must be approved by Area Manager first'
                ]);
            }

            $waste->update([
                'status' => 'Approved',
                'approve_accounting' => true
            ]);

            return response()->json([
                'message' => 'Waste has approved',
                'data' => new WasteResource($waste)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
