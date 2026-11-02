<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sumber Rezeki') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|space-grotesk:500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-shell">
        @include('layouts.sidebar')
        <div class="admin-main">
            <header class="app-topbar">
                <button class="mobile-menu" type="button" aria-label="Buka menu" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
                <div class="topbar-context"><span class="live-dot"></span><span>Sistem penjualan kayu</span></div>
                <div class="topbar-right">
                    <span class="topbar-notice" aria-label="Notifikasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4" /></svg></span>
                    <div class="topbar-profile"><div class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div><div class="topbar-user"><strong>{{ auth()->user()->name }}</strong><span>{{ ucfirst(auth()->user()->role) }}</span></div></div>
                </div>
            </header>
            @isset($header)<div class="page-heading">{{ $header }}</div>@endisset
            <main class="admin-content">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
