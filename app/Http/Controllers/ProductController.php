<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'cash_price' => ['required', 'numeric', 'min:0'],
            'credit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['nullable', 'date'],
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'unique:products,barcode,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'cash_price' => ['required', 'numeric', 'min:0'],
            'credit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    // Used by POS screen to look up a product by scanned barcode (AJAX)
    public function findByBarcode(Request $request)
    {
        $barcode = trim((string) $request->get('barcode'));

        // 1. Try an exact match first (best case: full barcode stored)
        $product = Product::where('barcode', $barcode)->where('is_active', true)->first();

        // 2. Fallback: some products only have a short/partial code on file
        //    (e.g. copied from Excel with just the last few digits, like
        //    "17579" instead of the full "9785517579"). If no exact match,
        //    check whether the SCANNED code ends with a stored short code.
        if (! $product && $barcode !== '') {
            $product = Product::where('is_active', true)
                ->where('barcode', '!=', '')
                ->whereRaw('? LIKE CONCAT(\'%\', barcode)', [$barcode])
                ->first();
        }

        if (! $product) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'cash_price' => (float) $product->cash_price,
                'credit_price' => (float) $product->credit_price,
                'stock_quantity' => $product->stock_quantity,
                'is_expired' => $product->isExpired(),
                'is_expiring_soon' => $product->isExpiringSoon(),
                'expiration_date' => $product->expiration_date?->format('M d, Y'),
            ],
        ]);
    }
}