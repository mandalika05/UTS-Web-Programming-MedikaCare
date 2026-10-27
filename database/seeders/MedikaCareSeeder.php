<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class MedikaCareSeeder extends Seeder
{
    public function run(): void
    {
        // Data kategori
        $categories = [
            'Obat-obatan',
            'Vitamin dan Suplemen',
            'Alat Kesehatan',
            'Perawatan Tubuh',
            'Produk Medis',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate([
                'name' => $name,
            ]);
        }

        // Data pemasok
        $suppliers = [
            [
                'name' => 'PT Sehat Sentosa',
                'contact_person' => 'Andi',
                'phone' => '081234567801',
                'address' => 'Banda Aceh',
            ],
            [
                'name' => 'CV Medika Jaya',
                'contact_person' => 'Budi',
                'phone' => '081234567802',
                'address' => 'Aceh Besar',
            ],
            [
                'name' => 'PT Farma Nusantara',
                'contact_person' => 'Siti',
                'phone' => '081234567803',
                'address' => 'Banda Aceh',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}