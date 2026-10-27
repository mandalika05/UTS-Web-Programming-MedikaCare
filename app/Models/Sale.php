<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id', 'user_id', 'customer_name',
        'sale_number', 'total', 'status',
        'payment_method', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    // Staff yang mengonfirmasi atau mencatat penjualan
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Pelanggan yang memiliki akun
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
