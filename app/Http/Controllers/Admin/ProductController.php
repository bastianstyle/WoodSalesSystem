<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
            ->when($request->string('wood_type')->toString(), fn ($q, $type) => $q->where('wood_type', $type))
            ->latest()->paginate(10)->withQueryString();
        $woodTypes = Product::query()->select('wood_type')->distinct()->orderBy('wood_type')->pluck('wood_type');

        return view('admin.products.index', compact('products', 'woodTypes'));
    }

    public function create(): View
    {
        return view('admin.products.create', ['product' => new Product(['is_active' => true])]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
