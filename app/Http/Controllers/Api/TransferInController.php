<?php

namespace App\Http\Controllers\Api;

use App\Models\TransferIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransferInResource;
use App\Http\Resources\TransferOutResource;
use App\Models\TransferOut;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransferInController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = TransferIn::with(['items', 'transferOuts'])
                ->where('destination_location_id', $user->store_location_id);
            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('transfer_in_number', 'like', "%{$search}%")
                        ->orWhere('transfer_in_number', 'like', "% {$search} %")
                        ->orWhere('transfer_out_number', 'like', "%{$search}%")
                        ->orWhereHas('sourceLocations', function ($q2) use ($search) {
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
            $transferIns = $query->latest()->paginate(10);

            return response()->json([
                'message' => 'Transfer in retrieved successfully',
                'data' => TransferInResource::collection($transferIns),
                'meta' => [
                    'current_page' => $transferIns->currentPage(),
                    'last_page' => $transferIns->lastPage(),
                    'total_records' => $transferIns->total(),
                    'per_page' => $transferIns->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve transfer in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transfer_out_number' => 'required|exists:transfer_outs,transfer_out_number',
            'receipt_date' => 'required|date',
            'transfer_date' => 'required|exists:transfer_outs,transfer_out_date',
            'receive_name' => 'required|string',
            'delivery_note' => 'nullable|array|max:5',
            'delivery_note.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:5012',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
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
            $deliveryNotePaths = [];
            if ($request->hasFile('delivery_note')) {
                foreach ($request->file('delivery_note') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('delivery_notes', $filename, 'public');
                    $deliveryNotePaths[] = $path;
                }
            }

            // Get Transfer Out yang sesuai lokasi user
            $user = $request->user();
            $transferOut = TransferOut::where('transfer_out_number', $request->transfer_out_number)
                ->where('destination_location_id', $user->store_location_id)
                ->first();

            if (!$transferOut) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transfer Out tidak ditemukan atau tidak sesuai lokasi user.'
                ], 403);
            }

            // Create a new GRPO record
            $transferIn = TransferIn::create([
                'transfer_in_number' => 'TI-' . (strlen($request->transfer_out_number) > 3 ? substr($request->transfer_out_number, 3) : $request->transfer_out_number),
                'transfer_out_number' => $request->transfer_out_number,
                'receipt_date' => $request->receipt_date,
                'transfer_date' => $transferOut->transfer_out_date,
                'source_location_id' => $transferOut->source_location_id,
                'destination_location_id' => $transferOut->destination_location_id,
                'receive_name' => Auth::check() && Auth::user() ? Auth::user()->name : null,
                'delivery_note' => json_encode($deliveryNotePaths),
                'notes' => $request->notes,
                'status' => $request->status ?? 'Received'
            ]);

            foreach ($request->items as $item) {
                $transferIn->items()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'transfer_in_number' => $transferIn->transfer_in_number,
                    'transfer_out_number' => $transferIn->transfer_out_number,
                ]);
            }

            // Update PO status
            $transferOut->update(['status' => 'Completed']);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transfer In created successfully',
                'data' => new TransferInResource($transferIn->load(['sourceLocations', 'destinationLocations']))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create Transfer In',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $transferIn = TransferIn::with(['transferOuts', 'items'])->findOrFail($id);

        return response()->json([
            'data' => new TransferInResource($transferIn)
        ]);
    }

    public function availableTransferOuts(Request $request)
    {
        try {

            $user = $request->user();
            $storeLocationId = $user->store_location_id;

            $transferOuts = TransferOut::where('destination_location_id', $storeLocationId)
                ->where('status', '!=', 'Completed')
                ->get();

            return response()->json([
                'data' => TransferOutResource::collection($transferOuts->load(['sourceLocations', 'destinationLocations']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve transfer in',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
