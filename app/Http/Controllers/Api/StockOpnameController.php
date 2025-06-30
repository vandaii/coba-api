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
    public function index(Request $request)
    {
        try {
            $query = StockOpname::with(['items', 'storeLocation']);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('stock_opname_number', 'like', "%{$search}%")
                        ->orWhere('stock_opname_date', 'like', "%{$search}%")
                        ->orWhere('counted_by', 'like', "%{$search}%")
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
            $stockOpnames = $query->latest()->paginate(10);

            return response()->json([
                'message' => 'Stock opname retrieved successfully',
                'data' => StockOpnameResource::collection($stockOpnames->load('storeLocation')),
                'meta' => [
                    'current_page' => $stockOpnames->currentPage(),
                    'last_page' => $stockOpnames->lastPage(),
                    'total_records' => $stockOpnames->total(),
                    'per_page' => $stockOpnames->perPage()
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
                'data' => new StockOpnameResource($stockOpname->load('storeLocation'))
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
