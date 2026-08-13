<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AuditLogger;
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

    // Generates a unique internal barcode for products with no manufacturer
    // barcode (e.g. loose rice, garlic, or other items sold by weight/count
    // that don't come with a printed barcode). Format: GP + 7-digit number.
    private function generateInternalBarcode(): string
    {
        do {
            $candidate = 'GP' . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $candidate)->exists());

        return $candidate;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['nullable', 'string', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'cash_price' => ['required', 'numeric', 'min:0'],
            'credit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['nullable', 'date'],
        ]);

        $generatedBarcode = false;
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateInternalBarcode();
            $generatedBarcode = true;
        }

        $product = Product::create($validated);

        AuditLogger::log('product_created', "Product created: {$product->name} (barcode {$product->barcode})" . ($generatedBarcode ? ' [auto-generated internal barcode]' : ''));

        $message = $generatedBarcode
            ? "Product added successfully. Internal barcode {$product->barcode} was generated — print a label for it below."
            : 'Product added successfully.';

        return redirect()->route('products.index')->with('success', $message);
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

        $changes = [];
        foreach (['name', 'cash_price', 'credit_price', 'stock_quantity'] as $field) {
            if ((string) $product->{$field} !== (string) $validated[$field]) {
                $changes[] = "{$field}: {$product->{$field}} → {$validated[$field]}";
            }
        }

        $product->update($validated);

        $changeSummary = $changes ? implode(', ', $changes) : 'no field changes';
        AuditLogger::log('product_updated', "Product updated: {$product->name} (barcode {$product->barcode}) — {$changeSummary}");

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $barcode = $product->barcode;

        $product->delete();

        AuditLogger::log('product_deleted', "Product deleted: {$name} (barcode {$barcode})");

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    // Printable barcode label (for products with an auto-generated internal
    // barcode, or any product you want a sticker for).
    public function label(Product $product)
    {
        return view('products.label', compact('product'));
    }

    public function bulkUploadForm()
    {
        return view('products.bulk-upload');
    }

    // Lets the user download a blank CSV with the correct headers to fill in
    public function bulkUploadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_template.csv"',
        ];

        $columns = ['barcode', 'name', 'cash_price', 'credit_price', 'stock_quantity', 'expiration_date'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['17579', 'Sample Product Name', '25.00', '27.00', '100', '2026-12-31']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Simpler template for Restock Mode - only barcode + stock_quantity needed
    public function bulkUploadRestockTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_restock_template.csv"',
        ];

        $columns = ['barcode', 'stock_quantity'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['17579', '30']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $addToStock = $request->boolean('add_to_stock');

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['csv_file' => 'Could not read the uploaded file.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'The file appears to be empty.']);
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $required = $addToStock
            ? ['barcode', 'stock_quantity']
            : ['barcode', 'name', 'cash_price', 'credit_price', 'stock_quantity'];
        $missing = array_diff($required, $header);

        if (! empty($missing)) {
            fclose($handle);
            return back()->withErrors([
                'csv_file' => 'Missing required column(s): ' . implode(', ', $missing) . '. Please use the template.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $rowErrors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            $barcode = trim((string) ($data['barcode'] ?? ''));
            $name = trim((string) ($data['name'] ?? ''));
            $cashPrice = $data['cash_price'] ?? null;
            $creditPrice = $data['credit_price'] ?? null;
            $stockQuantity = $data['stock_quantity'] ?? null;
            $expirationDate = trim((string) ($data['expiration_date'] ?? ''));

            $existing = Product::where('barcode', $barcode)->first();

            if ($addToStock && $existing) {
                if ($barcode === '' || ! is_numeric($stockQuantity)) {
                    $rowErrors[] = "Row {$rowNumber}: missing or invalid barcode/stock_quantity.";
                    continue;
                }

                $parsedExpiration = $existing->expiration_date?->format('Y-m-d');
                if ($expirationDate !== '') {
                    try {
                        $parsedExpiration = \Carbon\Carbon::parse($expirationDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $rowErrors[] = "Row {$rowNumber}: expiration date \"{$expirationDate}\" could not be understood, left unchanged.";
                    }
                }

                $existing->update([
                    'name' => $name !== '' ? $name : $existing->name,
                    'cash_price' => is_numeric($cashPrice) ? (float) $cashPrice : $existing->cash_price,
                    'credit_price' => is_numeric($creditPrice) ? (float) $creditPrice : $existing->credit_price,
                    'stock_quantity' => $existing->stock_quantity + (int) $stockQuantity,
                    'expiration_date' => $parsedExpiration,
                ]);
                $updated++;
                continue;
            }

            if ($barcode === '' || $name === '' || ! is_numeric($cashPrice) || ! is_numeric($creditPrice) || ! is_numeric($stockQuantity)) {
                $rowErrors[] = $addToStock
                    ? "Row {$rowNumber}: barcode \"{$barcode}\" not found yet — creating a new product needs name, cash_price, and credit_price too, not just stock_quantity."
                    : "Row {$rowNumber}: missing or invalid data (check barcode, name, prices, stock).";
                continue;
            }

            $parsedExpiration = null;
            if ($expirationDate !== '') {
                try {
                    $parsedExpiration = \Carbon\Carbon::parse($expirationDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $rowErrors[] = "Row {$rowNumber}: expiration date \"{$expirationDate}\" could not be understood, left blank.";
                }
            }

            $finalStock = (int) $stockQuantity;

            $payload = [
                'name' => $name,
                'cash_price' => (float) $cashPrice,
                'credit_price' => (float) $creditPrice,
                'stock_quantity' => $finalStock,
                'expiration_date' => $parsedExpiration,
            ];

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Product::create($payload + ['barcode' => $barcode]);
                $created++;
            }
        }

        fclose($handle);

        $modeNote = $addToStock ? ' (restock mode: quantities added to existing totals)' : '';
        $summary = "Bulk upload complete: {$created} added, {$updated} updated{$modeNote}.";

        AuditLogger::log('product_bulk_upload', $summary);

        return redirect()->route('products.index')->with('success', $summary)
            ->with('bulkUploadErrors', $rowErrors);
    }

    // Used by POS screen to look up a product by scanned barcode (AJAX)
    public function findByBarcode(Request $request)
    {
        $barcode = trim((string) $request->get('barcode'));

        $product = Product::where('barcode', $barcode)->where('is_active', true)->first();

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
