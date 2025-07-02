<?php

namespace App\Http\Controllers\Api;

use App\Models\GRPO;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\GRPOResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Controllers\Controller;

class GRPOController extends Controller
{
    public function index()
    {
        return GRPOResource::collection(GRPO::with('items')->get());
    }

    public function search(Request $request)
    {
        try {
            $query = GRPO::with(['items', 'purchaseOrder']);

            // Search by no_grpo
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('no_grpo', 'LIKE', "%{$search}%")
                    ->orWhere('no_po', 'LIKE', "%{$search}%")
                    ->orWhere('supplier', 'LIKE', "%{$search}%");
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('receive_date', [$request->start_date, $request->end_date]);
            }

            $grpos = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => GRPOResource::collection($grpos),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function shipping()
    {
        try {
            $shippingPOs = PurchaseOrder::where('status', 'shipping')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => PurchaseOrderResource::collection($shippingPOs)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showShipping($id)
    {
        // Ambil GRPO berdasarkan PO shipping tertentu
        $shippingPOs = PurchaseOrder::where('status', 'shipping')->findOrFail($id);

        return response()->json([
            'data' => new PurchaseOrderResource($shippingPOs)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_po' => 'required|exists:purchase_orders,no_purchase_order',
            'receive_date' => 'required|date',
            'expense_type' => 'required',
            'shipper_name' => 'required|string',
            'packing_slip' => 'nullable|array|max:5',
            'packing_slip.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:5012',
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
            $packingSlipPaths = [];
            if ($request->hasFile('packing_slip')) {
                foreach ($request->file('packing_slip') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('packing_slips', $filename, 'public');
                    $packingSlipPaths[] = $path;
                }
            }

            // Get Purchase Order
            $purchaseOrder = PurchaseOrder::where('no_purchase_order', $request->no_po)
                ->where('status', '!=', 'Received')
                ->firstOrFail();


            // Create a new GRPO record
            $grpo = GRPO::create([
                'no_grpo' => 'GR-' . (strlen($request->no_po) > 3 ? substr($request->no_po, 3) : $request->no_po),
                'no_po' => $request->no_po,
                'purchase_order_date' => $purchaseOrder->purchase_order_date,
                'receive_date' => $request->receive_date,
                'expense_type' => $request->expense_type,
                'receive_name' => Auth::check() && Auth::user() ? Auth::user()->name : null,
                'supplier' => $purchaseOrder->supplier,
                'shipper_name' => $purchaseOrder->shipper_by,
                'status' => $request->status ?? 'Received',
                'packing_slip' => json_encode($packingSlipPaths),
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

            DB::commit();

            $purchaseOrder->delete();

            return response()->json([
                'status' => true,
                'message' => 'GRPO created successfully',
                'data' => new GRPOResource($grpo),
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

    public function show($id)
    {
        $grpo = GRPO::with('items')->findOrFail($id);
        return new GRPOResource($grpo);
    }
}
