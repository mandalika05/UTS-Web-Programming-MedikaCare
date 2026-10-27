<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['PT Sehat Farma Distribusi', 'Bagian Penjualan', '021-555-0101', 'Jl. Contoh No. 1, Jakarta'],
            ['CV Mitra Medika Utama', 'Bagian Penjualan', '022-555-0102', 'Jl. Contoh No. 2, Bandung'],
            ['PT Alkes Nusantara Jaya', 'Bagian Penjualan', '031-555-0103', 'Jl. Contoh No. 3, Surabaya'],
        ];

        foreach ($suppliers as [$name, $contact, $phone, $address]) {
            Supplier::create([
                'name' => $name,
                'contact_person' => $contact,
                'phone' => $phone,
                'address' => $address,
            ]);
        }
    }
}