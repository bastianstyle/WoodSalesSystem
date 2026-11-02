@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div><x-input-label for="code" value="Kode Produk" /><x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $product->code)" required /><x-input-error :messages="$errors->get('code')" class="mt-2" /></div>
    <div><x-input-label for="name" value="Nama Kayu" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $product->name)" required /></div>
    <div><x-input-label for="wood_type" value="Jenis Kayu" /><x-text-input id="wood_type" name="wood_type" class="mt-1 block w-full" :value="old('wood_type', $product->wood_type)" required /></div>
    <div><x-input-label for="size" value="Ukuran" /><x-text-input id="size" name="size" class="mt-1 block w-full" :value="old('size', $product->size)" placeholder="Contoh: 4x6 cm" /></div>
    <div><x-input-label for="length" value="Panjang" /><x-text-input id="length" type="number" step="0.01" name="length" class="mt-1 block w-full" :value="old('length', $product->length)" /></div>
    <div><x-input-label for="width" value="Lebar" /><x-text-input id="width" type="number" step="0.01" name="width" class="mt-1 block w-full" :value="old('width', $product->width)" /></div>
    <div><x-input-label for="thickness" value="Tebal" /><x-text-input id="thickness" type="number" step="0.01" name="thickness" class="mt-1 block w-full" :value="old('thickness', $product->thickness)" /></div>
    <div><x-input-label for="unit" value="Satuan" /><select id="unit" name="unit" class="mt-1 block w-full border-gray-300 rounded-md"><option value="batang">Batang</option><option value="meter">Meter</option><option value="kubik">Kubik</option></select></div>
    <div><x-input-label for="selling_price" value="Harga Jual" /><x-text-input id="selling_price" type="number" step="0.01" min="0" name="selling_price" class="mt-1 block w-full" :value="old('selling_price', $product->selling_price)" required /></div>
    <div><x-input-label for="stock" value="Stok" /><x-text-input id="stock" type="number" step="0.001" min="0" name="stock" class="mt-1 block w-full" :value="old('stock', $product->stock ?? 0)" required /></div>
    <div><x-input-label for="minimum_stock" value="Minimum Stok" /><x-text-input id="minimum_stock" type="number" step="0.001" min="0" name="minimum_stock" class="mt-1 block w-full" :value="old('minimum_stock', $product->minimum_stock ?? 0)" required /></div>
</div>
<div class="mt-5"><x-input-label for="description" value="Deskripsi" /><textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', $product->description) }}</textarea></div>
<label class="mt-5 inline-flex items-center"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="rounded border-gray-300 text-indigo-600"><span class="ms-2 text-sm text-gray-600">Produk aktif</span></label>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-gray-600">Batal</a><x-primary-button>Simpan</x-primary-button></div>
