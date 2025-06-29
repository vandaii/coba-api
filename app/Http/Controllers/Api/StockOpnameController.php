<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockOpnameResource;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockOpnameController extends Controller
{
    public function index()
    {
        return StockOpnameResource::collection(StockOpname::with('items')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_opname_date' => 'required',
            'input_stock_date' => 'required',
            'counted_by' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.UoM' => 'required',
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

            $user = $request->user();
            $storeLocation = $user->store_location_id;

            $stockOpname = StockOpname::create([
                'stock_opname_date' => $request->stock_opname_date,
                'input_stock_date' => $request->input_stock_date,
                'counted_by' => $request->counted_by,
                'prepared_by' => Auth::check() ? Auth::user()->name : null,
                'store_location' => $storeLocation,
                'status' => $request->status ?? 'On Going'
            ]);

            foreach ($request->items as $item) {
                $stockOpname->items()->create([
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'UoM' => $item['UoM'],
                    'unit' => $item['unit'],
                    'stock_opname_number' => $stockOpname->stock_opname_number
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Stock Opname Successfully added',
                'data' => new StockOpnameResource($stockOpname->load('storeLocations'))
            ]);
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
        $stockOpname = StockOpname::with('items')->findOrFail($id);
        return new StockOpnameResource($stockOpname);
    }
}
