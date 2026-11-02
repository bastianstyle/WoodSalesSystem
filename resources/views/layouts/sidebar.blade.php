<aside class="app-sidebar">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <span class="brand-mark" aria-hidden="true">SR</span>
        <span><span class="brand-name">Sumber Rezeki</span><span class="brand-subtitle">WOOD SALES SYSTEM</span></span>
    </a>
    <nav class="sidebar-nav" aria-label="Navigasi utama">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
        <a href="{{ route('admin.sales.index') }}" class="sidebar-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}"><span class="nav-icon">↗</span><span>Penjualan</span><span class="nav-chevron">⌄</span></a>
        @if(request()->routeIs('admin.sales.*'))
            <div class="sidebar-subnav">
                <a href="{{ route('admin.sales.index') }}" class="{{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">Daftar Penjualan</a>
                <a href="{{ route('admin.sales.create') }}" class="{{ request()->routeIs('admin.sales.create') ? 'active' : '' }}">Transaksi Baru</a>
            </div>
        @endif
        <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><span class="nav-icon">▦</span><span>Stok Kayu</span></a>
        <a href="{{ route('admin.customers.index') }}" class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><span class="nav-icon">♙</span><span>Pelanggan</span></a>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.keys.index') }}" class="sidebar-link {{ request()->routeIs('admin.keys.*') ? 'active' : '' }}"><span class="nav-icon">⌁</span><span>Kunci RSA</span></a>
        @endif
    </nav>
    <div class="sidebar-bottom">
        <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><span class="nav-icon">⚙</span><span>Pengaturan</span></a>
        <div class="sidebar-footer">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-meta"><strong>{{ auth()->user()->name }}</strong><span>{{ ucfirst(auth()->user()->role) }}</span></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-btn" title="Keluar" aria-label="Keluar">↪</button></form>
        </div>
    </div>
</aside>
