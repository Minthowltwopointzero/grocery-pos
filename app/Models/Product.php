<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'barcode', 'name', 'cash_price', 'credit_price', 'stock_quantity', 'expiration_date', 'is_active',
    ];

    protected $casts = [
        'cash_price' => 'decimal:2',
        'credit_price' => 'decimal:2',
        'is_active' => 'boolean',
        'expiration_date' => 'date',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock(int $threshold = 10): bool
    {
        return $this->stock_quantity <= $threshold;
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function isExpiringSoon(int $daysThreshold = 30): bool
    {
        if (! $this->expiration_date || $this->isExpired()) {
            return false;
        }

        return now()->diffInDays($this->expiration_date, false) <= $daysThreshold;
    }
}
