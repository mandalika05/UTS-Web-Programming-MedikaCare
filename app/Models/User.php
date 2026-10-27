<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Memeriksa apakah user adalah admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Memeriksa apakah user adalah staff
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    // Memeriksa apakah user adalah pelanggan
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // Menampilkan label peran pengguna
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'staff' => 'Staff',
            default => 'Pelanggan',
        };
    }

    // Transaksi penjualan yang dicatat staff ini
    public function handledSales()
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    // Pesanan milik user sebagai pelanggan
    public function orders()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
