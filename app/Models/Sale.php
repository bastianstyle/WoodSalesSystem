<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_number', 'user_id', 'customer_id', 'subtotal', 'discount', 'grand_total', 'amount_paid', 'change_amount', 'status', 'issued_at'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'grand_total' => 'decimal:2', 'amount_paid' => 'decimal:2', 'change_amount' => 'decimal:2', 'issued_at' => 'datetime'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
