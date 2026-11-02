<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">RSA Key Management</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        @if(session('success'))<div class="rounded bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif
        <div class="rounded-lg bg-white p-6 shadow"><h3 class="font-semibold">Generate RSA key pair</h3><form method="POST" action="{{ route('admin.keys.store') }}" class="mt-4 flex gap-3">@csrf<input name="version" required placeholder="Contoh: v2" class="rounded border-gray-300"><button class="rounded bg-amber-700 px-4 py-2 text-white">Generate & Rotate</button></form><p class="mt-2 text-sm text-gray-500">Private key terenkripsi dan tidak pernah ditampilkan.</p></div>
        <div class="overflow-hidden rounded-lg bg-white shadow"><table class="min-w-full divide-y"><thead><tr class="text-left"><th class="p-4">Version</th><th class="p-4">Algorithm</th><th class="p-4">Size</th><th class="p-4">Status</th><th class="p-4">Generated</th></tr></thead><tbody class="divide-y">@forelse($keys as $key)<tr><td class="p-4">{{ $key->version }}</td><td class="p-4">{{ $key->key_algorithm }} / SHA-256</td><td class="p-4">{{ $key->key_size }} bit</td><td class="p-4">{{ strtoupper($key->status) }}</td><td class="p-4">{{ $key->generated_at->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada key.</td></tr>@endforelse</tbody></table></div>
    </div></div>
</x-app-layout>
