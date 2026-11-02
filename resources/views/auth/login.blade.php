<x-guest-layout>
<div class="login-heading">
    <span class="login-heading-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>
    </span>
    <span class="login-eyebrow">Area anggota</span>
    <h2>Selamat datang kembali</h2>
    <p>Masuk untuk mengelola penjualan dan stok kayu Anda.</p>
</div>

<x-auth-session-status class="login-session-status" :status="session('status')" />

<form method="POST" action="{{ route('login') }}" class="login-form">
    @csrf

    <div class="login-field">
        <label for="email">Alamat email</label>
        <div class="login-input-wrap">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input id="email" class="login-input" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="username">
        </div>
        <x-input-error :messages="$errors->get('email')" class="login-input-error" />
    </div>

    <div class="login-field">
        <div class="login-label-row"><label for="password">Kata sandi</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>
        <div class="login-input-wrap">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/></svg>
            <input id="password" class="login-input" type="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
        </div>
        <x-input-error :messages="$errors->get('password')" class="login-input-error" />
    </div>

    <label for="remember_me" class="login-remember">
        <input id="remember_me" type="checkbox" name="remember">
        <span>Ingat saya di perangkat ini</span>
    </label>

    <button class="login-submit" type="submit">Masuk ke Dashboard
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
    </button>
</form>

<div class="login-security"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg><span>Koneksi Anda aman dan terlindungi.</span></div>
</x-guest-layout>
