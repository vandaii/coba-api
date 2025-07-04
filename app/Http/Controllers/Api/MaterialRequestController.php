<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\MaterialRequestResource;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MaterialRequest::with(['materialRequestItems', 'storeLocation']);

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
                $itemCode = Item::where('item_code', $item['item_code'])->first();
                if (!$itemCode) {
                    throw new \Exception("Item not found: {$item['item_code']}");
                }

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
        $materialRequest = MaterialRequest::with(['materialRequestItems', 'storeLocation'])->findOrFail($id);
        return new MaterialRequestResource($materialRequest->load('storeLocation'));
    }

    public function reject(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role == 'User Outlet') {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 422);
        }

        $materialRequest = MaterialRequest::with(['materialRequestItems', 'storeLocation'])->findOrFail($id);
        $materialRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This request has been rejected',
        ]);
    }

    public function revision(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role == 'User Outlet') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $materialRequest = MaterialRequest::with(['materialRequestItems', 'storeLocation'])->findOrFail($id);
        $validator = Validator::make($request->all(), [
            'remark_revision' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        $materialRequest->update([
            'remark_revision' => $request->remark_revision,
            'status' => 'Draft'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'This request have revision',
            'data' => new MaterialRequestResource($materialRequest)
        ]);
    }

    public function update(Request $request, $id)
    {
        $materialRequest = MaterialRequest::with(['materialRequestItems', 'storeLocation'])->findOrFail($id);
        if ($materialRequest->status !== 'Draft') {
            return response()->json([
                'message' => 'Only draft can be update'
            ], 409);
        }

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

            $materialRequest->update([
                'request_date' => $request->request_date,
                'due_date' => $request->due_date,
                'store_location' => $storeLocation,
                'reason' => $request->reason,
                'status' => 'Pending',
                'approve_area_manager' => false,
                'approve_accounting' => false
            ]);

            if ($request->has('items')) {
                $materialRequest->materialRequestItems()->delete();

                foreach ($request->items as $item) {
                    $itemCode = Item::where('item_code', $item['item_code'])->first();
                    if (!$itemCode) {
                        throw new \Exception("Item not found: {$item['item_code']}");
                    }

                    $materialRequest->materialRequestItems()->create([
                        'item_code' => $item['item_code'],
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request has been updated.',
                    'data' => new MaterialRequestResource($materialRequest)
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create material request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
