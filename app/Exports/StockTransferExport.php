<?php

namespace App\Exports;

use App\Models\StockTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockTransferExport implements WithMultipleSheets
{
    public function __construct(private StockTransfer $transfer) {}

    public function sheets(): array
    {
        return [
            new StockTransferItemsSheet($this->transfer),
            new StockTransferSummarySheet($this->transfer),
        ];
    }
}

// ── Items sheet ───────────────────────────────────────────────────────────────

class StockTransferItemsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    private int $row = 0;

    public function __construct(private StockTransfer $transfer) {}

    public function title(): string { return 'Transfer Items'; }

    public function collection()
    {
        return $this->transfer->items;
    }

    public function headings(): array
    {
        return ['#', 'Product Code', 'Description', 'Qty Requested', 'Qty Approved', 'Buying Price', 'Selling Price', 'Tax Code', 'Notes'];
    }

    public function map($item): array
    {
        $this->row++;
        return [
            $this->row,
            $item->product_code,
            $item->product_description,
            $item->qty_requested,
            $item->qty_approved ?? $item->qty_requested,
            number_format($item->buying_price,  2),
            number_format($item->selling_price, 2),
            $item->tax_code ?? '',
            $item->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $this->transfer->items->count() + 1;
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:I{$last}" => ['borders' => ['allBorders' => ['borderStyle' => 'thin']]],
        ];
    }
}

// ── Summary sheet ─────────────────────────────────────────────────────────────

class StockTransferSummarySheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private StockTransfer $transfer) {}

    public function title(): string { return 'Transfer Summary'; }

    public function collection()
    {
        $t = $this->transfer;
        return collect([
            ['Transfer No',   $t->transfer_no],
            ['Type',          $t->transfer_type],
            ['Status',        $t->status],
            ['From Branch',   $t->fromBranch?->name ?? 'External'],
            ['To Branch',     $t->toBranch?->name ?? 'External'],
            ['Reference Doc', $t->reference_doc ?? '—'],
            ['Total Items',   $t->total_items],
            ['Total Qty',     $t->total_qty],
            ['Notes',         $t->notes ?? ''],
            ['Requested By',  $t->requestedBy?->name ?? '—'],
            ['Requested At',  $t->created_at->format('Y-m-d H:i')],
            ['Approved By',   $t->approvedBy?->name ?? '—'],
            ['Approved At',   $t->approved_at?->format('Y-m-d H:i') ?? '—'],
        ]);
    }

    public function headings(): array
    {
        return ['Field', 'Value'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']]],
            'A:A' => ['font' => ['bold' => true]],
        ];
    }
}
