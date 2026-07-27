<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SupplierController extends Controller implements HasMiddleware
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['name', 'contact_person', 'phone', 'email', 'status'];

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,manager,procurement', only: [
                'create', 'store', 'edit', 'update', 'toggleStatus',
            ]),
        ];
    }

    private function filteredSuppliersQuery(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredSuppliersQuery($request);
        $suppliers = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'name')
            ->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? Supplier::whereIn('id', $request->input('ids'))
            : $this->filteredSuppliersQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'name')
            ->get()
            ->map(fn (Supplier $s) => [
                'name' => $s->name,
                'contact_person' => $s->contact_person,
                'phone' => $s->phone,
                'email' => $s->email,
                'status' => ucfirst($s->status),
            ]);

        return $this->streamCsvExport('suppliers-' . now()->format('Ymd_His') . '.csv', [
            'name' => 'Name', 'contact_person' => 'Contact Person', 'phone' => 'Phone',
            'email' => 'Email', 'status' => 'Status',
        ], $rows);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'tin' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry_date' => ['nullable', 'date'],
            'accreditation_body' => ['nullable', 'string', 'max:255'],
            'mcaz_licensed_person' => ['nullable', 'string', 'max:255'],
            'wholesale_license_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate($this->rules());

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['status' => $supplier->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', "Supplier {$supplier->name} is now {$supplier->status}.");
    }
}
