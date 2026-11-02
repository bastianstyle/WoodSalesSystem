<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Invoice - Sumber Rezeki</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body.verify-page{min-height:100vh;margin:0;padding:32px 18px;background:#f7f6f3;color:#292522;font-family:Manrope,sans-serif}
        .verify-shell{max-width:760px;margin:0 auto}
        .verify-banner{position:relative;height:180px;overflow:hidden;border-radius:14px 14px 0 0;background:#e8dfd1}
        .verify-banner img{width:100%;height:100%;object-fit:cover}
        .verify-banner:after{position:absolute;inset:0;content:"";background:linear-gradient(90deg,rgba(35,29,23,.68),rgba(35,29,23,.08))}
        .verify-banner-copy{position:absolute;z-index:1;top:50%;left:28px;transform:translateY(-50%);color:#fff}
        .verify-brand{font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .verify-banner-copy p{margin:5px 0 0;color:#f4eee8;font-size:10px}
        .verify-card{position:relative;margin-top:-1px;padding:24px 28px 28px;border:1px solid #e7e2dc;border-radius:0 0 14px 14px;background:#fff;box-shadow:0 12px 35px #4b2e1714}
        .verify-heading h1{margin:0;color:#292522;font:700 23px 'Space Grotesk',sans-serif}
        .verify-heading p{margin:5px 0 20px;color:#817a73;font-size:11px}
        .verify-status{display:flex;align-items:center;gap:12px;padding:15px 17px;border-radius:9px}
        .verify-status.valid{background:#e8f5eb;color:#17653a}.verify-status.invalid{background:#fff0f0;color:#a72f39}.verify-status.warn{background:#fff7df;color:#946717}
        .verify-icon{display:grid;place-items:center;width:34px;height:34px;flex:0 0 auto;border-radius:50%;background:#fff;font-size:19px;font-weight:800}
        .verify-status strong{display:block;font-size:12px}.verify-status p{margin:3px 0 0;font-size:10px}
        .verify-details{display:grid;grid-template-columns:1fr 1fr;gap:0 22px;margin-top:18px}
        .verify-field{padding:11px 0;border-bottom:1px solid #eee9e3}.verify-field.hash-field{grid-column:1/-1}
        .verify-label{display:block;margin-bottom:5px;color:#817a73;font-size:9px}.verify-value{color:#292522;font-size:10px;font-weight:700}
        .verify-hash{margin:0;color:#625b54;font:9px/1.6 monospace;overflow-wrap:anywhere}
        .verify-footer{margin:19px 0 0;color:#8b847b;font-size:9px;text-align:center}
        @media(max-width:560px){body.verify-page{padding:16px 12px}.verify-banner{height:145px}.verify-card{padding:20px}.verify-details{grid-template-columns:1fr}.verify-field.hash-field{grid-column:auto}}
    </style>
</head>
<body class="verify-page">
    <main class="verify-shell">
        <div class="verify-banner">
            <img src="{{ asset('images/wood-stack.svg') }}" alt="Ilustrasi tumpukan kayu Sumber Rezeki">
            <div class="verify-banner-copy"><div class="verify-brand">Sumber Rezeki</div><p>Wood Sales System</p></div>
        </div>
        <section class="verify-card">
            <div class="verify-heading"><h1>Verifikasi E-Invoice</h1><p>Pemeriksaan integritas dokumen secara kriptografis.</p></div>
            <div class="verify-status {{ $status === 'VALID' ? 'valid' : ($status === 'REVOKED' ? 'warn' : 'invalid') }}">
                <span class="verify-icon" aria-hidden="true">{{ $status === 'VALID' ? '✓' : ($status === 'REVOKED' ? '!' : '×') }}</span>
                <div><strong>{{ $status === 'VALID' ? 'Invoice Valid' : $status }}</strong><p>{{ $status === 'VALID' ? 'Invoice asli dan data transaksi tidak berubah.' : ($status === 'REVOKED' ? 'Invoice ini telah dicabut.' : 'Invoice tidak dapat dinyatakan valid.') }}</p></div>
            </div>
            @if($invoice)
                <dl class="verify-details">
                    <div class="verify-field"><dt class="verify-label">Nomor invoice</dt><dd class="verify-value">{{ $invoice->sale->invoice_number }}</dd></div>
                    <div class="verify-field"><dt class="verify-label">Tanggal terbit</dt><dd class="verify-value">{{ $invoice->issued_at->format('d/m/Y H:i') }}</dd></div>
                    <div class="verify-field"><dt class="verify-label">Pelanggan</dt><dd class="verify-value">{{ $invoice->sale->customer->name }}</dd></div>
                    <div class="verify-field hash-field"><dt class="verify-label">SHA-256 payload</dt><dd class="verify-hash">{{ $invoice->canonical_payload_hash }}</dd></div>
                </dl>
            @endif
            <p class="verify-footer">Validasi dilakukan oleh backend Sumber Rezeki.</p>
        </section>
    </main>
</body>
</html>
