<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransferOutResource;
use App\Models\StoreLocation;
use App\Models\TransferOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransferOutController extends Controller
{
    public function index()
    {
        $transferOuts = TransferOut::with(['sourceLocations', 'destinationLocations'])->get();
        return TransferOutResource::collection($transferOuts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transfer_out_date' => 'required|date',
            'destination_location_id' => 'required',
            'delivery_note' => 'nullable|array|max:5',
            'delivery_note.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5012',
            'notes' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {

            //User Store
            $user = $request->user();
            $sourceLocation = $user->store_location_id;

            $deliveryNotePaths = [];
            if ($request->hasFile('delivery_note')) {
                foreach ($request->file('delivery_note') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('delivery_notes', $filename, 'public');
                    $deliveryNotePaths[] = $path;
                }
            }

            $transferOut = TransferOut::create([
                'transfer_out_number' => $request->transfer_out_number,
                'transfer_out_date' => $request->transfer_out_date,
                'source_location_id' => $sourceLocation,
                'destination_location_id' => $request->destination_location_id,
                'delivery_note' => json_encode($deliveryNotePaths),
                'note' => $request->note,
                'status' => $request->status ?? 'Pending',
            ]);

            foreach ($request->items as $item) {
                $transferOut->items()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'transfer_out_number' => $transferOut->transfer_out_number
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Transfer out created successfully',
                'data' => new TransferOutResource($transferOut->load(['sourceLocations', 'destinationLocations'])),
            ], 201);
        } catch (\Exception $e) {
            //Mengeluarkan data ketika gagal
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create transfer out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $transferOuts = TransferOut::with(['sourceLocations', 'destinationLocations'])->findOrFail($id);
        return new TransferOutResource($transferOuts);
    }
}
