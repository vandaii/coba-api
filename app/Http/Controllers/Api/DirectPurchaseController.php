<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\DirectPurchase;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\DirectPurchaseCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DirectPurchaseResource;

class DirectPurchaseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DirectPurchase::with(['items']);

            // Search Direct Purchase
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('no_direct_purchase', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%")
                        ->orWhere('expense_type', 'like', "%{$search}%");
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
            $directPurchases = $query->latest()->paginate(10);

            return response()->json([
                'message' => 'Direct purchases retrieved successfully',
                'data' => DirectPurchaseResource::collection($directPurchases),
                'meta' => [
                    'current_page' => $directPurchases->currentPage(),
                    'last_page' => $directPurchases->lastPage(),
                    'total_records' => $directPurchases->total(),
                    'per_page' => $directPurchases->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve direct purchases',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //Validasi inputan
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'supplier' => 'required|string',
            'expense_type' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.item_description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'purchase_proof' => 'nullable|array|max:5',
            'purchase_proof.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5012',
            'note' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //Memulai transaksi database
        DB::beginTransaction();
        try {

            $purchaseProofPaths = [];
            if ($request->hasFile('purchase_proof')) {
                foreach ($request->file('purchase_proof') as $index => $file) {
                    $filename = 'PurchaseProof-' . ($index) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('purchase_proofs', $filename, 'public');
                    $purchaseProofPaths[] = $path;
                }
            }
            //Membuat data Direct Purchase
            $directPurchase = DirectPurchase::create([
                'date' => $request->date,
                'supplier' => $request->supplier,
                'expense_type' => $request->expense_type ?? 'Inventory',
                'total_amount' => $request->total_amount,
                'purchase_proof' => json_encode($purchaseProofPaths),
                'note' => $request->note,
                'status' => $request->status ?? 'Pending Area Manager',
                'approve_area_manager' => $request->approve_area_manager ?? false,
                'approve_accounting' => $request->approve_accounting ?? false,
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                //Menghitung total harga per item
                $total_price = $item['quantity'] * $item['price'];
                //Membuat data item untuk Direct Purchase
                $directPurchase->items()->create([
                    'item_name' => $item['item_name'],
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'unit' => $item['unit'],
                    'total_price' => $total_price,
                ]);
                //Menghitung total harga keseluruhan
                $totalAmount += $total_price;
            }

            $directPurchase->update(['total_amount' => $totalAmount]);
            //Memasukkan data ketika berhasil
            DB::commit();

            return response()->json([
                'message' => 'Direct purchase created successfully',
                'data' => new DirectPurchaseResource($directPurchase)
            ], 201);
        } catch (\Exception $e) {
            //Mengeluarkan data ketika gagal
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create direct purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $directPurchase = DirectPurchase::with('items')->findOrFail($id);

        return new DirectPurchaseResource($directPurchase);
    }

    public function destroy($id)
    {
        $directPurchase = DirectPurchase::with('items')->findOrFail($id);

        if ($directPurchase->approve_area_manager == true && $directPurchase->approve_accounting == true) {
            return response()->json([
                'message' => 'Direct Purchase has been approved by Area Manager'
            ], 409);
        }

        $directPurchase->delete();
        return response()->json([
            'message' => 'Delete successfully'
        ]);
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

        $directPurchase = DirectPurchase::findOrFail($id);

        $directPurchase->update([
            'status' => 'Approved Area Manager',
            'approve_area_manager' => true,
        ]);

        return response()->json([
            'Message' => 'Area Manager Approved',
            'data' => new DirectPurchaseResource($directPurchase),
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

            $directPurchase = DirectPurchase::findOrFail($id);

            if (!$directPurchase->approve_area_manager) {
                return response()->json([
                    'status' => false,
                    'message' => 'Purchase must be approved by Area Manager first'
                ], 400);
            }

            // Check current status
            if ($directPurchase->status !== 'Approve Area Manager') {
                return response()->json([
                    'status' => false,
                    'message' => 'Purchase is not pending accounting approval'
                ], 400);
            }

            DB::beginTransaction();

            $directPurchase->update([
                'status' => 'Approved',
                'approve_accounting' => true,
            ]);

            DB::commit();

            return new DirectPurchaseResource($directPurchase);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectApprove(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role == 'User Outlet') {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 422);
        }

        $directPurchase = DirectPurchase::with('items')->findOrFail($id);

        if ($directPurchase->approve_area_manager == true) {
            if ($user->role == 'Approve Area Manager') {
                return response()->json([
                    'message' => "You've been approved this purchase"
                ], 422);
            }
        }

        $directPurchase->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'This purchase has been rejected'
        ]);
    }

    public function revision(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role == 'User Outlet') {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 422);
        }

        $directPurchase = DirectPurchase::with('items')->findOrFail($id);
        $validator = Validator::make($request->all(), [
            'remark_revision' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $directPurchase->update([
            'remark_revision' => $request->remark_revision,
            'status' => 'Draft'
        ]);

        return response()->json([
            'message' => 'This purchase have revision',
            'data' => new DirectPurchaseResource($directPurchase)
        ]);
    }

    public function update(Request $request, $id)
    {
        $directPurchase = DirectPurchase::with('items')->findOrFail($id);
        if ($directPurchase->status !== 'Draft') {
            return response()->json([
                'message' => 'Only draft can be update'
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'supplier' => 'required|string',
            'expense_type' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.item_description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'purchase_proof' => 'nullable|array|max:5',
            'purchase_proof.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5012',
            'note' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldProofs = json_decode($directPurchase->purchase_proof ?? '[]', true);
            $purchaseProofPaths = $oldProofs;
            $startIndex = count($oldProofs);

            if ($request->hasFile('purchase_proof')) {
                foreach ($request->file('purchase_proof') as $i => $file) {
                    $filename = 'PurchaseProof-' . ($startIndex + $i + 1) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('purchase_proofs', $filename, 'public');
                    $purchaseProofPaths[] = $path;
                }
            }

            $directPurchase->update([
                'date' => $request->date,
                'supplier' => $request->supplier,
                'expense_type' => $request->expense_type,
                'total_amount' => $request->total_amount,
                'purchase_proof' => json_encode($purchaseProofPaths),
                'note' => $request->note,
                'remark_revision' => $request->remark_revision,
                'status' => 'Pending Area Manager',
                'approve_area_manager' => false,
                'approve_accounting' => false
            ]);

            if ($request->has('items')) {
                $directPurchase->items()->delete();
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $total_price = $item['quantity'] * $item['price'];
                    $directPurchase->items()->create([
                        'item_name' => $item['item_name'],
                        'item_description' => $item['item_description'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'unit' => $item['unit'],
                        'total_price' => $total_price,
                    ]);
                    $totalAmount += $total_price;
                }
                $directPurchase->update(['total_amount' => $totalAmount]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Purchase successfully updated',
                'data' => new DirectPurchaseResource($directPurchase)
            ]);
        } catch (\Exception $e) {
            //Mengeluarkan data ketika gagal
            DB::rollback();
            return response()->json([
                'message' => 'Failed to update direct purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
