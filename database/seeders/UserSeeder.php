<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Rina Kartika', 'admin@medikacare.test', 'admin'],
            ['Budi Santoso', 'staff1@medikacare.test', 'staff'],
            ['Sari Wulandari', 'staff2@medikacare.test', 'staff'],
            ['Ahmad Fauzi', 'pelanggan@medikacare.test', 'customer'],
            ['Klinik Pratama Sehat Sentosa', 'klinik@medikacare.test', 'customer'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => $role,
            ]);
        }
    }
}
