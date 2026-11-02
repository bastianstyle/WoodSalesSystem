<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RsaKey;
use App\Services\Audit\AuditLogger;
use App\Services\Cryptography\InvoiceSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RsaKeyController extends Controller
{
    public function index(): View
    {
        return view('admin.keys.index', ['keys' => RsaKey::latest('generated_at')->paginate(10)]);
    }

    public function store(Request $request, InvoiceSignatureService $service): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'max:50', 'unique:rsa_keys,version']]);
        try {
            $key = $service->generateKeyPair($data['version']);
            app(AuditLogger::class)->record('rsa_key.created', $key, [
                'version' => $key->version,
                'key_size' => $key->key_size,
                'algorithm' => $key->key_algorithm,
            ], $request);

            return back()->with('success', 'RSA key berhasil dibuat dan key aktif sebelumnya dipensiunkan.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['version' => 'RSA key tidak dapat dibuat.']);
        }
    }
}
