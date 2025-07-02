<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PurchaseOrderService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PURCHASE_ORDER_API_URL');
    }

    public function getPO($id)
    {
        $response = Http::get("{$this->baseUrl}/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Gagal mengambil data PO dari Mock API');
    }
}
