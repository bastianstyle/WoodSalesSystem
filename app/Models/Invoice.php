<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = ['sale_id', 'public_token', 'canonical_payload_hash', 'canonical_payload', 'status', 'issued_at'];

    protected function casts(): array
    {
        return ['canonical_payload' => 'array', 'issued_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function digitalSignature(): HasOne
    {
        return $this->hasOne(DigitalSignature::class);
    }
}
