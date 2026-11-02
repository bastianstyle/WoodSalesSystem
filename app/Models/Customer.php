<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'phone', 'address', 'email', 'registered_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'date'];
    }
}
