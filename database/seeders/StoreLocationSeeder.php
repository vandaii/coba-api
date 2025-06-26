<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('store_locations')->insert([
            [
                'store_name' => 'HAUS Tangerang',
                'address' => 'Tangerang'
            ],
            [
                'store_name' => 'HAUS Cibubur',
                'address' => 'Cibubur'
            ]
        ]);
    }
}
