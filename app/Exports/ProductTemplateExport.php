<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            ['PRD-001', 'Amoxicillin 500mg Capsules', 'Antibiotics', 'Amoxicillin', 'Capsule', '500mg', '10x10', 'Box', 'Ambient', '3.50', '5.00', '20', '50', 'GSK', 'MCAZ-12345', '', 'Acme Pharma Wholesalers', 'BN-2026-001', '2027-06-30', '100', '3.50'],
            ['PRD-002', 'Paracetamol 500mg Tablets',  'Analgesics',  'Paracetamol', 'Tablet',  '500mg', '10x10', 'Box', 'Ambient', '1.20', '2.00', '30', '100', 'Cipla', 'MCAZ-67890', '', 'Acme Pharma Wholesalers', 'BN-2026-002', '2027-09-30', '200', '1.20'],
        ];
    }

    public function headings(): array
    {
        return [
            'product_code', 'product_description', 'category', 'generic_name', 'dosage_form',
            'strength', 'pack_size', 'unit_of_measure', 'storage_condition',
            'buying_price', 'selling_price', 'reorder_point', 'reorder_qty',
            'manufacturer', 'registration_number', 'controlled_substance_schedule', 'default_supplier',
            'initial_batch_number', 'initial_expiry_date', 'initial_qty', 'initial_unit_cost',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A2:U3')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:U3')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD4E8D4']]],
        ]);

        $sheet->mergeCells('A4:U4');
        $sheet->setCellValue('A4', 'Duplicate product_code updates the existing catalogue entry. initial_batch_number/initial_expiry_date/initial_qty/initial_unit_cost only create a starting batch for NEW products — leave them blank to skip, and leave them off entirely on updates since existing stock is managed through Goods Received Notes.');
        $sheet->getStyle('A4:U4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF64748B'], 'size' => 9],
        ]);

        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(24);

        return [];
    }

    public function title(): string
    {
        return 'Product Import Template';
    }
}
