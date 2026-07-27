<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ClientController extends Controller implements HasMiddleware
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['name', 'contact_person', 'phone', 'email', 'vat_number', 'tin'];

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,manager,sales,finance', only: ['create', 'store', 'edit', 'update']),
        ];
    }

    /**
     * AJAX quick-lookup, kept for any integrations that search clients by
     * name/VAT/TIN (e.g. buyer lookups elsewhere in the app).
     */
    public function search(Request $request)
    {
        $q = $request->input('q', '');

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('vat_number', 'like', "%{$q}%")
            ->orWhere('tin', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    private function filteredClientsQuery(Request $request)
    {
        $query = Client::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('vat_number', 'like', "%{$search}%")
                  ->orWhere('tin', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredClientsQuery($request);
        $clients = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'name')
            ->paginate(20)->withQueryString();

        $stats = [
            'total' => Client::count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? Client::whereIn('id', $request->input('ids'))
            : $this->filteredClientsQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'name')
            ->get()
            ->map(fn (Client $c) => [
                'name' => $c->name,
                'contact_person' => $c->contact_person,
                'phone' => $c->phone,
                'email' => $c->email,
                'vat_number' => $c->vat_number,
                'tin' => $c->tin,
            ]);

        return $this->streamCsvExport('clients-' . now()->format('Ymd_His') . '.csv', [
            'name' => 'Name', 'contact_person' => 'Contact Person', 'phone' => 'Phone',
            'email' => 'Email', 'vat_number' => 'VAT Number', 'tin' => 'TIN',
        ], $rows);
    }

    public function create()
    {
        return view('clients.create');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'vat_number'     => 'nullable|string|max:9',
            'tin'            => 'nullable|string|max:10',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'credit_limit'   => 'nullable|numeric|min:0',
            'province'       => 'nullable|string|max:100',
            'city'           => 'nullable|string|max:100',
            'district'       => 'nullable|string|max:100',
            'street'         => 'nullable|string|max:100',
            'house_no'       => 'nullable|string|max:20',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $client = Client::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'client' => $client]);
        }

        return redirect()->route('clients.show', $client)->with('success', 'Client added successfully.');
    }

    public function show(Client $client)
    {
        $client->load([
            'salesOrders' => fn ($q) => $q->latest()->limit(10),
            'salesInvoices' => fn ($q) => $q->latest('invoice_date')->limit(10),
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate($this->rules());

        $client->update($data);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }
}
