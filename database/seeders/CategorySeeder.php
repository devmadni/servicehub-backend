<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'AC Technician', 'slug' => 'ac-technician'],
            ['name' => 'Plumber', 'slug' => 'plumber'],
            ['name' => 'Electrician', 'slug' => 'electrician'],
            ['name' => 'Cleaning Service', 'slug' => 'cleaning-service'],
            ['name' => 'Tutor', 'slug' => 'tutor'],
            ['name' => 'Carpenter', 'slug' => 'carpenter'],
            ['name' => 'Painter', 'slug' => 'painter'],
            ['name' => 'Driver', 'slug' => 'driver'],
            ['name' => 'Mechanic', 'slug' => 'mechanic'],
            ['name' => 'Beautician', 'slug' => 'beautician'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
