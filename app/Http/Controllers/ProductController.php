<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,inventory_manager,procurement', only: [
                'create', 'store', 'edit', 'update', 'destroy', 'bulkImport',
            ]),
        ];
    }

    /**
     * Default ZIMRA tax fields for pharma-created products.
     * Pharma products are Exempt by default — the fiscal POS side reads
     * these columns directly, so they must always have a value.
     */
    private function defaultTaxFields(float $sellingPrice): array
    {
        $ex = config('zimra.tax.EX', ['id' => 1, 'percent' => 0.0]);

        return [
            'tax_code'              => 'EX',
            'tax_id'                => $ex['id'],
            'tax_percentage'        => $ex['percent'],
            'tax_amount'            => 0.00,
            'sales_amount_with_tax' => round($sellingPrice, 2),
            'hs_code'               => '00000000',
        ];
    }

    public function index(Request $request)
    {
        $query = Stock::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_description', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('quantity', '<=', 'reorder_point')->where('reorder_point', '>', 0);
        }

        $products   = $query->orderBy('product_description')->paginate(20)->withQueryString();
        $categories = Stock::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $stats = [
            'total'      => Stock::count(),
            'low_stock'  => Stock::whereColumn('quantity', '<=', 'reorder_point')->where('reorder_point', '>', 0)->count(),
            'out_of_stock' => Stock::where('quantity', 0)->count(),
            'categories' => $categories->count(),
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();

        return view('products.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code'            => 'required|string|max:100|unique:stocks,product_code',
            'product_description'     => 'required|string|max:255',
            'category'                => 'nullable|string|max:100',
            'generic_name'            => 'nullable|string|max:255',
            'manufacturer'            => 'nullable|string|max:255',
            'registration_number'     => 'nullable|string|max:100',
            'controlled_substance_schedule' => 'nullable|string|max:50',
            'dosage_form'             => 'nullable|string|max:100',
            'strength'                => 'nullable|string|max:100',
            'pack_size'               => 'nullable|string|max:100',
            'unit_of_measure'         => 'nullable|string|max:50',
            'storage_condition'       => 'nullable|string|max:100',
            'buying_price'            => 'required|numeric|min:0',
            'selling_price'           => 'required|numeric|min:0',
            'reorder_point'           => 'nullable|integer|min:0',
            'reorder_qty'             => 'nullable|integer|min:0',
            'requires_batch_tracking' => 'nullable|boolean',
            'default_supplier_id'     => 'nullable|exists:suppliers,id',
        ]);

        $product = Stock::create(array_merge($validated, [
            'quantity'                => 0,
            'reorder_point'           => $validated['reorder_point'] ?? 0,
            'reorder_qty'             => $validated['reorder_qty'] ?? 0,
            'requires_batch_tracking' => $request->boolean('requires_batch_tracking', true),
        ], $this->defaultTaxFields((float) $validated['selling_price'])));

        StockAuditLog::record(
            action: StockAuditLog::STOCK_IN,
            productCode: $product->product_code,
            productDescription: $product->product_description,
            qtyBefore: 0,
            qtyAfter: 0,
            notes: 'Product created in catalogue'
        );

        return redirect()->route('products.index')->with('success', 'Product added to catalogue.');
    }

    public function show(Stock $product)
    {
        $product->load(['batches' => fn ($q) => $q->with('branch')->orderBy('expiry_date')]);

        return view('products.show', compact('product'));
    }

    public function edit(Stock $product)
    {
        $suppliers = Supplier::where('status', 'active')->get();

        return view('products.edit', compact('product', 'suppliers'));
    }

    public function update(Request $request, Stock $product)
    {
        $validated = $request->validate([
            'product_code'            => 'required|string|max:100|unique:stocks,product_code,' . $product->id,
            'product_description'     => 'required|string|max:255',
            'category'                => 'nullable|string|max:100',
            'generic_name'            => 'nullable|string|max:255',
            'manufacturer'            => 'nullable|string|max:255',
            'registration_number'     => 'nullable|string|max:100',
            'controlled_substance_schedule' => 'nullable|string|max:50',
            'dosage_form'             => 'nullable|string|max:100',
            'strength'                => 'nullable|string|max:100',
            'pack_size'               => 'nullable|string|max:100',
            'unit_of_measure'         => 'nullable|string|max:50',
            'storage_condition'       => 'nullable|string|max:100',
            'buying_price'            => 'required|numeric|min:0',
            'selling_price'           => 'required|numeric|min:0',
            'reorder_point'           => 'nullable|integer|min:0',
            'reorder_qty'             => 'nullable|integer|min:0',
            'requires_batch_tracking' => 'nullable|boolean',
            'default_supplier_id'     => 'nullable|exists:suppliers,id',
        ]);

        $product->update(array_merge($validated, [
            'reorder_point'           => $validated['reorder_point'] ?? 0,
            'reorder_qty'             => $validated['reorder_qty'] ?? 0,
            'requires_batch_tracking' => $request->boolean('requires_batch_tracking', true),
            'sales_amount_with_tax'   => round((float) $validated['selling_price'], 2),
        ]));

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Stock $product)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'Only administrators can delete products.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new \App\Imports\ProductImport();
        Excel::import($import, $request->file('file'));

        $results = $import->getResults();
        $total   = $results['imported'] + $results['updated'];

        $msg = $total > 0
            ? "Import complete: {$results['imported']} new product(s), {$results['updated']} updated."
            : 'No records were imported. Check your file format or column headers.';

        if ($results['skipped'] > 0) {
            $msg .= " {$results['skipped']} row(s) skipped.";
        }

        return redirect()->route('products.index')
            ->with($total > 0 ? 'success' : 'error', $msg)
            ->with('import_errors', $results['errors']);
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\ProductTemplateExport(), 'product-import-template.xlsx');
    }
}
