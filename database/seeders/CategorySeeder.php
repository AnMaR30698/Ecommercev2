<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
                    'Electronics',
                    'Computers',
                    'Phones & Tablets',
                    'TV, Audio & Video',
                    'Fashion',
                    'Men\'s Clothing',
                    'Women\'s Clothing',
                    'Shoes',
                    'Home & Garden',
                    'Furniture',
                    'Appliances',
                    'Kitchen & Dining',
                    'Fast Food',
                    'Burgers',
                    'restaurants',
                    'Food And Beverages',
                    'Markets & Daily Needs',
                    'Clothing And Accessories',
                    'Electronics',
                    'Health And Beauty',
                    'Meet And Grills',
                    'Eastern & Western Cuisine',
                    'Sea Food',
                    'Desserts'
        ];

        foreach ($categories as $categoryName) {
            Category::create(['name' => $categoryName,
                              'image'=>'']);
        }
    }
}
