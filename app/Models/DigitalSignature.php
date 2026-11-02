<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends Model
{
    protected $fillable = ['invoice_id', 'rsa_key_id', 'signature_base64', 'hash_algorithm', 'signature_algorithm', 'payload_hash', 'signed_at'];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function rsaKey(): BelongsTo
    {
        return $this->belongsTo(RsaKey::class);
    }
}
