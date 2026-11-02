<x-app-layout>
    <section class="dashboard-hero">
        <img src="{{ asset('images/dashboard-wood.png') }}" alt="Tumpukan kayu gelondongan di area hutan" class="hero-image">
        <div class="hero-shade"></div>
        <div class="hero-copy">
            <span class="hero-eyebrow">{{ now()->translatedFormat('l, d F Y') }}</span>
            <h1>Selamat datang, {{ explode(' ', auth()->user()->name)[0] }} <span aria-hidden="true">👋</span></h1>
            <p>Kelola penjualan kayu dengan mudah dan efisien.</p>
        </div>
        <a href="{{ route('admin.sales.create') }}" class="hero-action">＋ Transaksi Baru</a>
    </section>

    <section class="stats-grid" aria-label="Ringkasan usaha">
        <article class="stat-card stat-green">
            <div class="stat-card-top"><span class="stat-label">Total Penjualan Hari Ini</span><span class="stat-icon">↗</span></div>
            <div class="stat-value">Rp {{ number_format($overview['todayRevenue'], 0, ',', '.') }}</div>
            <div class="stat-foot"><span class="stat-indicator">↗</span> Pendapatan hari ini</div>
        </article>
        <article class="stat-card">
            <div class="stat-card-top"><span class="stat-label">Total Transaksi</span><span class="stat-icon">▤</span></div>
            <div class="stat-value">{{ number_format($overview['activeOrders']) }}</div>
            <div class="stat-foot">Transaksi yang diterbitkan</div>
        </article>
        <article class="stat-card stat-blue">
            <div class="stat-card-top"><span class="stat-label">Stok Produk</span><span class="stat-icon">▦</span></div>
            <div class="stat-value">{{ number_format($overview['totalStock'], 0, ',', '.') }} <small>unit</small></div>
            <div class="stat-foot">{{ $overview['totalProducts'] }} jenis produk aktif</div>
        </article>
    </section>

    <div class="dashboard-panels">
        <section class="analytics-card revenue-card">
            <div class="panel-head">
                <div><h2 class="panel-title">Grafik Penjualan</h2><p class="panel-muted">Pendapatan 7 hari terakhir</p></div>
                <span class="period-pill">7 Hari</span>
            </div>
            <div class="chart-area" role="img" aria-label="Grafik pendapatan tujuh hari terakhir">
                @php
                    $trendMax = max(1, $overview['trend']->max('total'));
                    $trendLastIndex = max(1, $overview['trend']->count() - 1);
                    $trendPoints = $overview['trend']->values()->map(fn ($point, $index) =>
                        (24 + ($index / $trendLastIndex * 652)).','. (132 - ($point['total'] / $trendMax * 100))
                    );
                @endphp
                <div class="chart-guides">
                    <span>Rp {{ number_format($overview['trend']->max('total'), 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($overview['trend']->max('total') / 2, 0, ',', '.') }}</span>
                    <span>Rp 0</span>
                </div>
                <svg class="trend-line-chart" viewBox="0 0 700 160" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="trend-line" points="{{ $trendPoints->implode(' ') }}" />
                    @foreach($overview['trend']->values() as $index => $point)
                        <circle class="trend-point" cx="{{ 24 + ($index / $trendLastIndex * 652) }}" cy="{{ 132 - ($point['total'] / $trendMax * 100) }}" r="4">
                            <title>{{ $point['date'] }}: Rp {{ number_format($point['total'], 0, ',', '.') }}</title>
                        </circle>
                    @endforeach
                </svg>
                <div class="trend-labels">
                    @foreach($overview['trend'] as $point)<span>{{ $point['label'] }}</span>@endforeach
                </div>
            </div>
        </section>

        <section class="analytics-card recent-card">
            <div class="panel-head">
                <div><h2 class="panel-title">Transaksi Terbaru</h2><p class="panel-muted">Aktivitas penjualan terkini</p></div>
                <a href="{{ route('admin.sales.index') }}" class="panel-link">Lihat semua →</a>
            </div>
            <div class="recent-list">
                @forelse($overview['recentSales'] as $sale)
                    <a class="recent-row" href="{{ route('admin.sales.show', $sale) }}">
                        <span class="recent-thumb"><x-wood-image :type="$sale->details->first()?->wood_type_snapshot ?? ''" /></span>
                        <span class="recent-info"><strong>{{ $sale->invoice_number }}</strong><small>{{ $sale->customer->name }}</small></span>
                        <span class="recent-amount"><strong>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</strong><small>{{ $sale->issued_at->diffForHumans() }}</small></span>
                    </a>
                @empty
                    <div class="empty-state">Belum ada transaksi. Buat transaksi pertama untuk mulai mencatat penjualan.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="dashboard-table-card">
        <div class="panel-head table-panel-head">
            <div><h2 class="panel-title">Stok Perlu Perhatian</h2><p class="panel-muted">Produk yang stoknya sudah mencapai batas minimum</p></div>
            <a href="{{ route('admin.products.index') }}" class="panel-link">Lihat stok kayu →</a>
        </div>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Nama Produk</th><th>Kategori</th><th>Ukuran</th><th>Stok Tersedia</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($overview['lowStockProducts'] as $product)
                        <tr>
                            <td><span class="table-product"><x-wood-image :type="$product->wood_type" :alt="$product->wood_type" /><strong>{{ $product->name }}</strong></span></td>
                            <td>{{ $product->wood_type }}</td>
                            <td>{{ $product->size ?: '-' }}</td>
                            <td>{{ number_format($product->stock, 0, ',', '.') }} {{ $product->unit }}</td>
                            <td><span class="stock-warning">Stok menipis</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="table-empty">Semua stok produk dalam kondisi aman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
