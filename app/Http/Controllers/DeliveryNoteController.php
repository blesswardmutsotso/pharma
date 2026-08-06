<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\DeliveryNote;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['delivery_note_no', 'delivery_date'];

    private function filteredDeliveryNotesQuery(Request $request)
    {
        $query = DeliveryNote::with(['client', 'salesOrder', 'deliveredBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('delivery_note_no', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('salesOrder', fn ($sq) => $sq->where('so_number', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredDeliveryNotesQuery($request);
        $deliveryNotes = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'delivery_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('delivery-notes.index', compact('deliveryNotes'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? DeliveryNote::with(['client', 'salesOrder', 'deliveredBy'])->whereIn('id', $request->input('ids'))
            : $this->filteredDeliveryNotesQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'delivery_date', 'desc')
            ->get()
            ->map(fn (DeliveryNote $d) => [
                'number' => $d->delivery_note_no,
                'sales_order' => $d->salesOrder?->so_number,
                'client' => $d->client?->name,
                'date' => $d->delivery_date?->format('Y-m-d'),
                'delivered_by' => $d->deliveredBy?->name,
            ]);

        return $this->streamCsvExport('delivery-notes-' . now()->format('Ymd_His') . '.csv', [
            'number' => 'Delivery Note No.', 'sales_order' => 'Sales Order', 'client' => 'Client',
            'date' => 'Delivery Date', 'delivered_by' => 'Delivered By',
        ], $rows);
    }

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['client', 'salesOrder', 'branch', 'deliveredBy', 'items']);

        return view('delivery-notes.show', compact('deliveryNote'));
    }

    public function pdf(Request $request, DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['client', 'salesOrder', 'branch', 'deliveredBy', 'items']);

        $isDuplicate = $deliveryNote->print_count > 0;
        $deliveryNote->increment('print_count');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.delivery-note', [
            'deliveryNote' => $deliveryNote,
            'isDuplicate' => $isDuplicate,
        ])->setPaper('a4', 'landscape');

        $filename = "{$deliveryNote->delivery_note_no}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
