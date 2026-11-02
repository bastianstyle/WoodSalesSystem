<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sumber Rezeki') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|space-grotesk:500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-page">
    <main class="guest-layout">
        <section class="guest-visual" aria-label="Sumber Rezeki Wood Sales System">
            <img class="guest-visual-image" src="{{ asset('images/wood-stack.svg') }}" alt="">
            <div class="guest-visual-shade"></div>
            <a class="guest-brand" href="{{ route('login') }}">
                <span class="guest-brand-mark">SR</span>
                <span><strong>Sumber Rezeki</strong><small>WOOD SALES SYSTEM</small></span>
            </a>
            <div class="guest-visual-copy">
                <span class="guest-eyebrow">Mitra terpercaya kebutuhan kayu</span>
                <h1>Kayu berkualitas,<br>usaha lebih mudah.</h1>
                <p>Kelola stok, pelanggan, dan transaksi penjualan dalam satu sistem yang praktis.</p>
            </div>
            <div class="guest-visual-footer"><span class="guest-live-dot"></span> Sistem penjualan kayu terpadu</div>
        </section>
        <section class="guest-form-side">
            <div class="guest-auth-card">
                {{ $slot }}
            </div>
            <p class="guest-copyright">© {{ now()->year }} Sumber Rezeki · Wood Sales System</p>
        </section>
    </main>
</body>
</html>
