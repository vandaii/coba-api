<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GRPOController extends Controller
{
    public function index() {}

    public function store(Request $request)
    {
        $validator = Validator::make([
            'no_po' => 'required',
            'no_grpo' => 'required',
            'receive_date' => 'required',
            'expense_type' => 'required',
            'shipper_name' => 'required',
            'receive_name' => 'required',
            'supplier' => 'required',
            'item_code' => 'required',
            'item_name' => 'required',
            'item_quantity' => 'required',
            'item_unit' => 'required',
            'packing_slip' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'note' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    }
}
