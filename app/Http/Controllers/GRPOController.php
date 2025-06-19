<?php

namespace App\Http\Controllers;

use App\Models\GRPO;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GRPOController extends Controller
{
    public function index()
    {
        return GRPO::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_po' => 'required|exists:purchase_orders,no_purchase_order',
            'receive_date' => 'required|date',
            'expense_type' => 'required',
            'shipper_name' => 'required|string',
            'packing_slip' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'notes' => 'nullable|string',
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
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $packingSlip = null;
            if ($request->hasFile('packing_slip')) {
                $packingSlip = $request->file('packing_slip')->store('packing_slips', 'public');
            }

            // Get Purchase Order
            $purchaseOrder = PurchaseOrder::where('no_purchase_order', $request->no_po)
                ->where('status', '!=', 'Received')
                ->firstOrFail();


            // Create a new GRPO record
            $grpo = GRPO::create([
                'no_grpo' => 'GR-' . (strlen($request->no_po) > 3 ? substr($request->no_po, 3) : $request->no_po),
                'no_po' => $request->no_po,
                'receive_date' => $request->receive_date,
                'expense_type' => $request->expense_type,
                'receive_name' => Auth::check() && Auth::user() ? Auth::user()->name : null,
                'supplier' => $purchaseOrder->supplier,
                'shipper_name' => $request->shipper_name,
                'packing_slip' => $packingSlip,
                'notes' => $request->notes
            ]);

            // Get item from items table
            // $item = Item::where('item_code', $itemData['item_code'])->first();

            // if (!$item) {
            //     throw new \Exception("Item not found: {$itemData['item_code']}");
            // }

            foreach ($request->items as $itemData) {
                $grpo->items()->create([
                    'item_code' => $itemData['item_code'],
                    'item_name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'no_grpo' => $grpo->no_grpo
                ]);
            }


            // Update PO status
            $purchaseOrder->update(['status' => 'Received']);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'GRPO created successfully',
                'data' => [
                    'grpo' => $grpo,
                    'items' => $grpo->items,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create GRPO',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
