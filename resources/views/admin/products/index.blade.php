<x-app-layout>
    <div class="page-toolbar">
        <div><h1 class="page-title">Data Stok Kayu</h1><p class="page-subtitle">Pantau ketersediaan dan harga produk kayu.</p></div>
        <a href="{{ route('admin.products.create') }}" class="wood-button">＋ Tambah Produk</a>
    </div>
    @if(session('success'))<div class="form-alert success-alert">{{ session('success') }}</div>@endif
    <section class="page-card inventory-card">
        <form class="inventory-filters" method="GET">
            <label class="product-search"><span aria-hidden="true">⌕</span><input name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama kayu..." class="wood-input"></label>
            <select name="wood_type" class="wood-input" aria-label="Filter kategori">
                <option value="">Semua Kategori</option>
                @foreach($woodTypes as $type)<option value="{{ $type }}" @selected(request('wood_type') === $type)>{{ $type }}</option>@endforeach
            </select>
            <button type="submit" class="wood-button">Cari</button>
        </form>
        <div class="table-scroll">
            <table class="inventory-table">
                <thead><tr><th>Nama Produk</th><th>Kategori</th><th>Ukuran</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><span class="table-product"><x-wood-image :type="$product->wood_type" :alt="$product->wood_type" class="inventory-thumb" /><span><strong>{{ $product->name }}</strong><small>{{ $product->code }}</small></span></span></td>
                            <td>{{ $product->wood_type }}</td>
                            <td>{{ $product->size ?: '-' }}</td>
                            <td><strong class="{{ $product->is_low_stock ? 'low-stock-value' : '' }}">{{ number_format($product->stock, 0, ',', '.') }} {{ $product->unit }}</strong>@if($product->is_low_stock)<small class="inventory-low-label">Stok menipis</small>@endif</td>
                            <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td class="inventory-actions"><a href="{{ route('admin.products.edit', $product) }}" class="table-action">Edit</a>@if(auth()->user()->role === 'admin')<form method="POST" action="{{ route('admin.products.destroy', $product) }}">@csrf @method('DELETE')<button class="table-action danger-action" onclick="return confirm('Hapus produk ini?')">Hapus</button></form>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="table-empty">Belum ada produk kayu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="inventory-pagination">{{ $products->links() }}</div>
    </section>
</x-app-layout>
