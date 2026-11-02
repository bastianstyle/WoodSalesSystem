<x-app-layout>
    <div class="sales-page-heading">
        <div><h1 class="page-title">Transaksi Baru</h1><div class="sales-breadcrumb"><a href="{{ route('admin.sales.index') }}">Penjualan</a><span>›</span><span>Transaksi Baru</span></div></div>
        <a href="{{ route('admin.sales.index') }}" class="sales-history-link">Daftar Penjualan <span aria-hidden="true">→</span></a>
    </div>

    @if($errors->any())
        <div class="form-alert" role="alert"><strong>Transaksi belum dapat disimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.sales.store') }}" id="sale-form">
        @csrf
        <div class="sales-create-grid">
            <section class="page-card product-picker">
                <div class="section-title-row"><div><h2 class="section-title">Pilih Produk Kayu</h2><p class="page-subtitle">Klik produk untuk menambahkannya ke keranjang.</p></div><span class="product-count">{{ $products->count() }} produk</span></div>
                <label class="product-search"><span aria-hidden="true">⌕</span><input id="product-search" class="wood-input" type="search" placeholder="Cari produk kayu..." autocomplete="off"></label>
                <div class="wood-filter-tabs" role="group" aria-label="Filter jenis kayu">
                    <button class="wood-filter active" type="button" data-wood-filter="all">Semua</button>
                    @foreach($woodTypes as $woodType)
                        <button class="wood-filter" type="button" data-wood-filter="{{ strtolower($woodType) }}">{{ $woodType }}</button>
                    @endforeach
                </div>
                <div class="product-grid" id="product-grid">
                    @foreach($products as $product)
                        <button
                            type="button"
                            class="product-tile"
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}"
                            data-product-type="{{ $product->wood_type }}"
                            data-product-size="{{ $product->size }}"
                            data-product-price="{{ $product->selling_price }}"
                            data-product-stock="{{ $product->stock }}"
                            data-product-wood="{{ strtolower($product->wood_type) }}"
                            data-product-search="{{ strtolower($product->name.' '.$product->wood_type.' '.$product->size) }}"
                        >
                            <span class="product-image"><x-wood-image :type="$product->wood_type" :alt="$product->wood_type" /></span>
                            <strong>{{ $product->name }}</strong>
                            <small>{{ $product->wood_type }}@if($product->size) · {{ $product->size }}@endif</small>
                            <em>Rp {{ number_format($product->selling_price, 0, ',', '.') }}/{{ $product->unit }}</em>
                            <span class="product-stock">Stok {{ number_format($product->stock, 0, ',', '.') }} {{ $product->unit }}</span>
                        </button>
                    @endforeach
                    @if($products->isEmpty())
                        <div class="empty-state">Belum ada produk aktif dengan stok tersedia.</div>
                    @endif
                </div>
                <div class="empty-search" id="empty-search" hidden>Produk tidak ditemukan.</div>
            </section>

            <aside class="page-card cart-panel">
                <div class="section-title-row"><div><h2 class="section-title">Keranjang Transaksi</h2><p class="page-subtitle"><span id="cart-count">0</span> produk dipilih</p></div><button class="clear-cart" type="button" id="clear-cart">Hapus Semua</button></div>
                <label class="form-field"><span>Pelanggan</span>
                    <select class="wood-input" name="customer_id" id="customer_id" required>
                        <option value="">Pilih pelanggan</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->code }} - {{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="cart-items" id="cart-items"><div class="cart-empty">Keranjang masih kosong.<br>Pilih produk di sebelah kiri.</div></div>
                <div id="cart-inputs"></div>
                <div class="cart-summary">
                    <div><span>Subtotal</span><strong id="cart-subtotal">Rp 0</strong></div>
                    <label class="summary-discount"><span>Diskon</span><input class="wood-input" type="number" min="0" step="0.01" name="discount" id="discount" value="{{ old('discount', 0) }}"></label>
                    <div class="tax-information"><span>Pajak (11%) <small>informasi saja</small></span><strong id="cart-tax">Rp 0</strong></div>
                    <p class="tax-note">Pajak ditampilkan sebagai informasi dan tidak ditambahkan ke total transaksi.</p>
                    <div class="cart-total"><span>Total Pembayaran</span><strong id="cart-total">Rp 0</strong></div>
                </div>
                <label class="form-field payment-field"><span>Jumlah Dibayar</span><input class="wood-input" type="number" min="0" step="0.01" name="amount_paid" id="amount_paid" value="{{ old('amount_paid') }}" required></label>
                <div class="cart-actions">
                    <button class="print-cart-button" type="button" id="print-cart" aria-label="Cetak keranjang" title="Cetak keranjang"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg></button>
                    <button class="wood-button checkout-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14v14H5z"/><path d="M8 3h8v4H8zM9 12h6m-6 3h6"/><path d="m16 17 2 2 4-5"/></svg>Bayar Sekarang <span aria-hidden="true">→</span></button>
                </div>
            </aside>
        </div>
    </form>

    <script>
        (() => {
            const productGrid = document.getElementById('product-grid');
            const cartContainer = document.getElementById('cart-items');
            const hiddenInputs = document.getElementById('cart-inputs');
            const amountInput = document.getElementById('amount_paid');
            const discountInput = document.getElementById('discount');
            const searchInput = document.getElementById('product-search');
            const filterButtons = document.querySelectorAll('[data-wood-filter]');
            const cart = new Map();
            const currency = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
            let amountWasEdited = @json(old('amount_paid') !== null);
            let activeWoodFilter = 'all';
            const initialItems = @json(is_array(old('items')) ? old('items') : []);

            const formatRupiah = (amount) => `Rp ${currency.format(amount)}`;
            const totalFor = (item) => item.price * item.quantity;

            function renderCart() {
                cartContainer.replaceChildren();
                cartContainer.classList.remove('cart-needs-item');
                hiddenInputs.replaceChildren();
                let subtotal = 0;
                let index = 0;

                if (cart.size === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'cart-empty';
                    empty.textContent = 'Keranjang masih kosong. Pilih produk di sebelah kiri.';
                    empty.style.whiteSpace = 'pre-line';
                    cartContainer.append(empty);
                }

                cart.forEach((item, id) => {
                    subtotal += totalFor(item);
                    const row = document.createElement('div');
                    row.className = 'cart-item';
                    const image = document.createElement('span');
                    image.className = 'cart-thumb';
                    const imageElement = document.createElement('img');
                    imageElement.src = item.image;
                    imageElement.alt = '';
                    image.append(imageElement);

                    const detail = document.createElement('span');
                    detail.className = 'cart-item-info';
                    const name = document.createElement('strong');
                    name.textContent = item.name;
                    const price = document.createElement('small');
                    price.textContent = `${formatRupiah(item.price)} / ${item.type}`;
                    detail.append(name, price);

                    const quantity = document.createElement('span');
                    quantity.className = 'cart-quantity-value';
                    quantity.textContent = item.quantity;
                    const quantityControl = document.createElement('span');
                    quantityControl.className = 'quantity-control';
                    const decrease = document.createElement('button');
                    decrease.type = 'button';
                    decrease.textContent = '−';
                    decrease.setAttribute('aria-label', `Kurangi ${item.name}`);
                    decrease.disabled = item.quantity <= 0.001;
                    decrease.addEventListener('click', () => {
                        item.quantity = Math.max(0.001, Math.round((item.quantity - 1) * 1000) / 1000);
                        renderCart();
                    });
                    const increase = document.createElement('button');
                    increase.type = 'button';
                    increase.textContent = '+';
                    increase.setAttribute('aria-label', `Tambah ${item.name}`);
                    increase.disabled = item.quantity >= item.stock;
                    increase.addEventListener('click', () => {
                        item.quantity = Math.min(item.stock, Math.round((item.quantity + 1) * 1000) / 1000);
                        renderCart();
                    });
                    quantityControl.append(decrease, quantity, increase);

                    const linePrice = document.createElement('strong');
                    linePrice.className = 'cart-line-total';
                    linePrice.textContent = formatRupiah(totalFor(item));
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'remove-item';
                    remove.textContent = '×';
                    remove.setAttribute('aria-label', `Hapus ${item.name}`);
                    remove.addEventListener('click', () => { cart.delete(id); renderCart(); });
                    row.append(image, detail, quantityControl, linePrice, remove);
                    cartContainer.append(row);

                    const productInput = document.createElement('input');
                    productInput.type = 'hidden';
                    productInput.name = `items[${index}][product_id]`;
                    productInput.value = id;
                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = `items[${index}][quantity]`;
                    quantityInput.value = item.quantity;
                    hiddenInputs.append(productInput, quantityInput);
                    index += 1;
                });

                const discount = Math.max(0, Number(discountInput.value) || 0);
                const total = Math.max(0, subtotal - discount);
                document.getElementById('cart-subtotal').textContent = formatRupiah(subtotal);
                document.getElementById('cart-tax').textContent = formatRupiah(subtotal * 0.11);
                document.getElementById('cart-total').textContent = formatRupiah(total);
                document.getElementById('cart-count').textContent = cart.size;
                if (!amountWasEdited) amountInput.value = total.toFixed(2);
            }

            productGrid.querySelectorAll('[data-product-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.productId;
                    const existing = cart.get(id);
                    if (existing) {
                        existing.quantity = Math.min(existing.stock, existing.quantity + 1);
                    } else {
                        cart.set(id, {
                            name: button.dataset.productName,
                            type: button.dataset.productType,
                            price: Number(button.dataset.productPrice),
                            stock: Number(button.dataset.productStock),
                            image: button.querySelector('img').src,
                            quantity: 1,
                        });
                    }
                    renderCart();
                });
            });

            function filterProducts() {
                const search = searchInput.value.trim().toLowerCase();
                let visible = 0;
                productGrid.querySelectorAll('[data-product-id]').forEach((button) => {
                    const matchesSearch = button.dataset.productSearch.includes(search);
                    const matchesWood = activeWoodFilter === 'all' || button.dataset.productWood === activeWoodFilter;
                    const matches = matchesSearch && matchesWood;
                    button.hidden = !matches;
                    if (matches) visible += 1;
                });
                document.getElementById('empty-search').hidden = visible > 0;
            }

            searchInput.addEventListener('input', filterProducts);
            filterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activeWoodFilter = button.dataset.woodFilter;
                    filterButtons.forEach((filter) => filter.classList.toggle('active', filter === button));
                    filterProducts();
                });
            });

            discountInput.addEventListener('input', renderCart);
            amountInput.addEventListener('input', () => { amountWasEdited = true; });
            document.getElementById('clear-cart').addEventListener('click', () => { cart.clear(); renderCart(); });
            document.getElementById('print-cart').addEventListener('click', () => window.print());
            document.getElementById('sale-form').addEventListener('submit', (event) => {
                if (cart.size === 0) {
                    event.preventDefault();
                    cartContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    cartContainer.classList.add('cart-needs-item');
                }
            });

            initialItems.forEach((oldItem) => {
                const button = productGrid.querySelector(`[data-product-id="${Number(oldItem.product_id)}"]`);
                if (!button) return;
                cart.set(button.dataset.productId, {
                    name: button.dataset.productName,
                    type: button.dataset.productType,
                    price: Number(button.dataset.productPrice),
                    stock: Number(button.dataset.productStock),
                    image: button.querySelector('img').src,
                    quantity: Math.min(Number(button.dataset.productStock), Number(oldItem.quantity) || 1),
                });
            });
            renderCart();
        })();
    </script>
</x-app-layout>
