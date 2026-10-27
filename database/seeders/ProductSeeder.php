<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [kategori, sku, nama, harga beli, harga jual, stok, stok minimum]
        $items = [
            ['Obat Bebas', 'OBT-001', 'Paracetamol 500 mg (strip)', 2500, 4000, 120, 20],
            ['Obat Bebas', 'OBT-002', 'Antasida (strip)', 3000, 5000, 80, 15],
            ['Obat Bebas', 'OBT-003', 'Sirup Batuk 60 ml', 9000, 13000, 40, 10],
            ['Obat Bebas', 'OBT-004', 'Salep Luka 5 g', 6500, 9500, 8, 10],
            ['Obat Bebas', 'OBT-005', 'Minyak Kayu Putih 60 ml', 14000, 19000, 35, 10],
            ['Obat Bebas', 'OBT-006', 'Oralit (sachet)', 1200, 2000, 0, 20],

            ['Obat Keras', 'KRS-001', 'Amoxicillin 500 mg (strip)', 7000, 11000, 60, 15],
            ['Obat Keras', 'KRS-002', 'Metformin 500 mg (strip)', 4000, 7000, 50, 15],
            ['Obat Keras', 'KRS-003', 'Amlodipine 5 mg (strip)', 5000, 8500, 45, 15],
            ['Obat Keras', 'KRS-004', 'Omeprazole 20 mg (strip)', 9000, 14000, 12, 15],

            ['Vitamin & Suplemen', 'VIT-001', 'Vitamin C 500 mg (botol 30)', 25000, 35000, 50, 10],
            ['Vitamin & Suplemen', 'VIT-002', 'Multivitamin Dewasa (botol)', 38000, 52000, 30, 8],
            ['Vitamin & Suplemen', 'VIT-003', 'Vitamin D3 1000 IU (botol)', 45000, 62000, 6, 8],
            ['Vitamin & Suplemen', 'VIT-004', 'Zinc 20 mg (strip)', 8000, 12000, 40, 10],

            ['Alat Kesehatan', 'ALK-001', 'Termometer Digital', 22000, 35000, 25, 5],
            ['Alat Kesehatan', 'ALK-002', 'Tensimeter Digital', 220000, 295000, 10, 3],
            ['Alat Kesehatan', 'ALK-003', 'Masker Medis (box 50)', 18000, 28000, 60, 10],
            ['Alat Kesehatan', 'ALK-004', 'Kasa Steril (pack)', 4000, 7000, 70, 15],
            ['Alat Kesehatan', 'ALK-005', 'Plester Luka (box)', 5000, 9000, 45, 10],
            ['Alat Kesehatan', 'ALK-006', 'Hand Sanitizer 100 ml', 8000, 13000, 3, 10],
            ['Alat Kesehatan', 'ALK-007', 'Oximeter Jari', 85000, 125000, 8, 3],
        ];

        foreach ($items as [$category, $sku, $name, $buy, $sell, $stock, $min]) {
            Product::create([
                'category_id' => Category::where('name', $category)->value('id'),
                'sku' => $sku,
                'name' => $name,
                'purchase_price' => $buy,
                'selling_price' => $sell,
                'stock' => $stock,
                'minimum_stock' => $min,
            ]);
        }
    }
}
