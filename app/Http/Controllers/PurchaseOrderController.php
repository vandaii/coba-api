<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrder::all();
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::all()->findOrFail($id);
        return $purchaseOrder;
    }
}
