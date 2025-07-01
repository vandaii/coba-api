<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialRequestResource;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MaterialRequest::with(['items', 'storeLocation']);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('request_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
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
                'data' => MaterialRequestResource::collection($materialRequests->load('storeLocation')),
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
            'request_date' => 'required',
            'due_date' => 'required',
            'reason' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $storeLocation = $user->store_location_id;

            $materialRequest = MaterialRequest::create([
                'request_date' => $request->request_date,
                'due_date' => $request->due_date,
                'store_location' => $storeLocation,
                'reason' => $request->reason,
                'status' => $request->status ?? 'Pending',
                'approve_area_manager' => $request->approve_area_manager ?? false,
                'approve_accounting' => $request->approve_accounting ?? false
            ]);

            foreach ($request->items as $item) {
                $materialRequest->items()->create([
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Material request successfully added',
                'data' => new MaterialRequestResource($materialRequest->load('storeLocation'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create material request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveAreaManager(Request $request, $id)
    {

        $user = $request->user();

        if ($user->role !== 'Area Manager') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only Area Manager can approve.'
            ], 403);
        }

        $materialRequest = MaterialRequest::findOrFail($id);

        $materialRequest->update([
            'status' => 'Approved Area Manager',
            'approve_area_manager' => true,
        ]);

        return response()->json([
            'Message' => 'Area Manager Approved',
            'data' => new MaterialRequestResource($materialRequest),
        ]);
    }

    public function approveAccounting(Request $request, $id)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Accounting') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only Accounting can approve.'
                ], 403);
            }

            $materialRequest = MaterialRequest::findOrFail($id);

            if (!$materialRequest->approve_area_manager) {
                return response()->json([
                    'status' => false,
                    'message' => 'Purchase must be approved by Area Manager first'
                ], 400);
            }

            // Check current status
            if ($materialRequest->status !== 'Approved Area Manager') {
                return response()->json([
                    'status' => false,
                    'message' => 'Request is not pending accounting approval'
                ], 400);
            }

            DB::beginTransaction();

            $materialRequest->update([
                'status' => 'Approved',
                'approve_accounting' => true,
            ]);

            DB::commit();

            return new MaterialRequestResource($materialRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $materialRequest = MaterialRequest::with(['items', 'storeLocation'])->findOrFail($id);
        return new MaterialRequestResource($materialRequest->load('storeLocation'));
    }
}
