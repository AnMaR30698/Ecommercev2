<?php

namespace Database\Seeders;

use App\Models\DeliveryCost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the delivery costs based on distance
        $deliveryCosts = [
            ['distance' => 5, 'cost' => 10.00],
            ['distance' => 10, 'cost' => 15.00],
            ['distance' => 15, 'cost' => 20.00],
            // Add more entries as needed
        ];

        // Insert the delivery costs into the database
        foreach ($deliveryCosts as $cost) {
            DeliveryCost::create($cost);
        }

    }
}
