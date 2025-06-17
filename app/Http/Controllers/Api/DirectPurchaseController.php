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
    public function index()
    {
        return new DirectPurchaseCollection(DirectPurchase::all());
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
            'purchase_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'note' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //Memulai transaksi database
        DB::beginTransaction();
        try {
            //Membuat data Direct Purchase
            $directPurchase = DirectPurchase::create([
                'date' => $request->date,
                'supplier' => $request->supplier,
                'expense_type' => $request->expense_type ?? 'Inventory',
                'total_amount' => $request->total_amount,
                'purchase_proof' => $request->purchase_proof,
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
                    'total_price' => $total_price
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

    public function approveAreaManager(Request $request, $id)
    {
        $directPurchase = DirectPurchase::with('items')->findOrFail($id);

        $directPurchase->update([
            'approve_area_manager' => $request->approve_area_manager ?? true,
        ]);

        if ($directPurchase->approve_area_manager === true) {
            $directPurchase->update([
                'status' => 'Approve Area Manager'
            ]);
        }

        return response()->json([
            'Message' => 'Area Manager Approved',
            'data' => new DirectPurchaseResource($directPurchase),
        ]);
    }
}
