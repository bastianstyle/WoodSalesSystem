@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div><x-input-label for="code" value="Kode Pelanggan" /><x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $customer->code)" required /><x-input-error :messages="$errors->get('code')" class="mt-2" /></div>
    <div><x-input-label for="name" value="Nama Pelanggan" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $customer->name)" required /></div>
    <div><x-input-label for="phone" value="Nomor Telepon" /><x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $customer->phone)" required /></div>
    <div><x-input-label for="email" value="Email (opsional)" /><x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $customer->email)" /></div>
    <div><x-input-label for="registered_at" value="Tanggal Registrasi" /><x-text-input id="registered_at" type="date" name="registered_at" class="mt-1 block w-full" :value="old('registered_at', optional($customer->registered_at)->format('Y-m-d'))" required /></div>
</div>
<div class="mt-5"><x-input-label for="address" value="Alamat" /><textarea id="address" name="address" class="mt-1 block w-full border-gray-300 rounded-md" required>{{ old('address', $customer->address) }}</textarea></div>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('admin.customers.index') }}" class="px-4 py-2 text-gray-600">Batal</a><x-primary-button>Simpan</x-primary-button></div>
