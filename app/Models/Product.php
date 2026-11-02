<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'wood_type', 'size', 'length', 'width', 'thickness',
        'unit', 'selling_price', 'stock', 'minimum_stock', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:2', 'width' => 'decimal:2', 'thickness' => 'decimal:2',
            'selling_price' => 'decimal:2', 'stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3', 'is_active' => 'boolean',
        ];
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }
}
