<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no', 'user_id', 'customer_id', 'payment_type',
        'total_amount', 'amount_paid', 'change_amount', 'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateInvoiceNo(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (self::whereDate('created_at', now())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
