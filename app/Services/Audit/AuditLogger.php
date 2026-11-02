<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function record(string $action, ?Model $model = null, array $metadata = [], ?Request $request = null): AuditLog
    {
        $request ??= request();
        unset($metadata['private_key'], $metadata['encrypted_private_key'], $metadata['password'], $metadata['signature_base64']);

        return AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
