<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Waste;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WasteController extends Controller
{
    public function index()
    {
        return Waste::with('items', 'storeLocation')->get();
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
                'data' => $waste
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
        return $waste;
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
                'data' => $waste
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
                'data' => $waste
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
