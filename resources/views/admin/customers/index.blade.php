<x-app-layout>
    <div class="page-toolbar">
        <div><h1 class="page-title">Pelanggan</h1><p class="page-subtitle">Kelola informasi dan kontak pelanggan.</p></div>
        <a href="{{ route('admin.customers.create') }}" class="wood-button">＋ Tambah Pelanggan</a>
    </div>
    @if(session('success'))<div class="form-alert success-alert">{{ session('success') }}</div>@endif
    <section class="page-card inventory-card">
        <form class="inventory-filters" method="GET">
            <label class="product-search"><span aria-hidden="true">⌕</span><input name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau telepon..." class="wood-input"></label>
            <button type="submit" class="wood-button">Cari</button>
        </form>
        <div class="table-scroll">
            <table class="inventory-table customers-table">
                <thead><tr><th>Pelanggan</th><th>Kontak</th><th>Alamat</th><th class="customer-actions-heading">Aksi</th></tr></thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><span class="customer-name">{{ $customer->name }}</span><small class="customer-code">{{ $customer->code }}</small></td>
                            <td>{{ $customer->phone }}<small class="customer-code">{{ $customer->email ?: '-' }}</small></td>
                            <td>{{ $customer->address }}</td>
                            <td>
                                <div class="customer-actions">
                                    <a class="icon-action edit-icon-action" href="{{ route('admin.customers.edit', $customer) }}" aria-label="Edit {{ $customer->name }}" title="Edit pelanggan">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}">
                                            @csrf @method('DELETE')
                                            <button class="icon-action delete-icon-action" type="submit" aria-label="Hapus {{ $customer->name }}" title="Hapus pelanggan" onclick="return confirm('Hapus pelanggan ini?')">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5m4-5v5"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="table-empty">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="inventory-pagination">{{ $customers->links() }}</div>
    </section>
</x-app-layout>
