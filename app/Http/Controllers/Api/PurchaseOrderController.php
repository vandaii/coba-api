<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrderResource::collection(PurchaseOrder::all());
    }

    public function show($noPo)
    {
        $purchaseOrder = PurchaseOrder::with('purchaseOrderItems')->where('purchase_order_number', $noPo)->first();
        return new PurchaseOrderResource($purchaseOrder);
    }
}
