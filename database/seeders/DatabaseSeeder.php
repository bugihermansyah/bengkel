<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Categories
        $categories = \App\Models\Category::factory(5)->create();

        // Create Suppliers
        $suppliers = \App\Models\Supplier::factory(5)->create();

        // Create Racks
        $racks = \App\Models\Rack::factory(5)->create();

        // Create Customers
        $customers = \App\Models\Customer::factory(5)->create();

        // Create Vehicles
        \App\Models\Vehicle::factory(10)->recycle($customers)->create();

        // Create Products
        \App\Models\Product::factory(100)
            ->recycle($categories)
            ->recycle($suppliers)
            ->recycle($racks)
            ->create();
    }
}
