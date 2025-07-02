<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are items to use
        $items = Item::all();
        if ($items->count() === 0) {
            $this->command->warn('No items found. Please seed items first.');
            return;
        }

        // Create 10 purchase orders
        for ($i = 0; $i < 10; $i++) {
            $expenseType = ['Inventory', 'Non Inventory'][rand(0, 1)];
            $po = PurchaseOrder::create([
                'purchase_order_date' => now()->subDays(rand(0, 30)),
                'expense_type' => $expenseType,
                'supplier' => 'Supplier ' . ($i + 1),
                'shipper_by' => 'Shipper ' . ($i + 1),
                'status' => 'Shipping',
            ]);

            // Add 2-5 items to each purchase order
            $poItems = $items->random(rand(2, 5));
            foreach ($poItems as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_number' => $po->purchase_order_number,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'quantity' => rand(1, 20),
                    'unit' => $item->UoM ?? 'PCS',
                ]);
            }
        }
    }
}
