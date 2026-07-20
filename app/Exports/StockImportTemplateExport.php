<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockImportTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Collection $stocks) {}

    public function title(): string { return 'Stock Transfer Template'; }

    public function collection(): Collection
    {
        return $this->stocks;
    }

    public function headings(): array
    {
        return ['product_code', 'product_description', 'qty_requested', 'notes', 'current_stock', 'buying_price', 'selling_price'];
    }

    public function map($stock): array
    {
        return [
            $stock->product_code,
            $stock->product_description,
            '',   // qty_requested — to be filled in
            '',   // notes
            $stock->quantity,
            number_format($stock->buying_price,  2),
            number_format($stock->selling_price, 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
