<?php

namespace App\Http\Controllers\Api;

use App\Models\TransferIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TransferOut;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransferInController extends Controller
{
    public function index()
    {
        return TransferIn::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transfer_out_number' => 'required|exists:transfer_outs,transfer_out_number',
            'receipt_date' => 'required|date',
            'transfer_date' => 'required|exists:transfer_outs, transfer_out_date',
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

            // Get Transfer Out
            $user = $request->user();
            $transferOut = TransferOut::where('transfer_out_number', $request->transfer_out_number)
                ->where('status', '!=', 'Received')
                ->where('destination_location_id', $user->store_location_id)
                ->firstOrFail();


            // Create a new GRPO record
            $transferIn = TransferIn::create([
                'transfer_in_number' => 'GR-' . (strlen($request->no_po) > 3 ? substr($request->no_po, 3) : $request->no_po),
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
                    'no_grpo' => $transferIn->transfer_in_number
                ]);
            }


            // Update PO status
            $transferOut->update(['status' => 'Completed']);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'GRPO created successfully',
                'data' => $transferIn,
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
