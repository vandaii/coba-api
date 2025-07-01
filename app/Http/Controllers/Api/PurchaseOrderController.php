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

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::all()->findOrFail($id);
        return new PurchaseOrderResource($purchaseOrder);
    }
}
