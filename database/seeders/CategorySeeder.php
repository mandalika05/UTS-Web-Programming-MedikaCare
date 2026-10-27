<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Obat Bebas',
            'Obat Keras',
            'Vitamin & Suplemen',
            'Alat Kesehatan',
        ];

        foreach ($names as $name) {
            Category::create(['name' => $name]);
        }
    }
}