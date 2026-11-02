<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RsaKey extends Model
{
    protected $fillable = ['version', 'public_key', 'encrypted_private_key', 'key_algorithm', 'key_size', 'status', 'generated_at', 'retired_at'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime', 'retired_at' => 'datetime'];
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class);
    }
}
