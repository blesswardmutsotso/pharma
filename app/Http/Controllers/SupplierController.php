<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,procurement', only: [
                'create', 'store', 'edit', 'update', 'toggleStatus',
            ]),
        ];
    }

    public function index(Request $request)
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

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
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
