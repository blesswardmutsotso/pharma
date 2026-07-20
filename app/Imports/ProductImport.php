<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\StockAuditLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    private int   $imported = 0;
    private int   $updated  = 0;
    private int   $skipped  = 0;
    private array $errors   = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum      = $index + 2;
            $productCode = trim((string) ($row['product_code'] ?? ''));
            $productDesc = trim((string) ($row['product_description'] ?? ''));

            if ($productCode === '') {
                continue;
            }

            $buyPrice  = $row['buying_price']  ?? '';
            $sellPrice = $row['selling_price'] ?? '';

            $errs = [];
            if ($productDesc === '')      $errs[] = 'product_description required';
            if (!is_numeric($buyPrice))   $errs[] = 'buying_price must be numeric';
            if (!is_numeric($sellPrice))  $errs[] = 'selling_price must be numeric';

            if (!empty($errs)) {
                $this->skipped++;
                $this->errors[] = "Row {$rowNum} ({$productCode}): " . implode('; ', $errs);
                continue;
            }

            $ex = config('zimra.tax.EX', ['id' => 1, 'percent' => 0.0]);

            $attributes = [
                'product_description'     => $productDesc,
                'category'                => trim((string) ($row['category'] ?? '')) ?: null,
                'generic_name'            => trim((string) ($row['generic_name'] ?? '')) ?: null,
                'dosage_form'             => trim((string) ($row['dosage_form'] ?? '')) ?: null,
                'strength'                => trim((string) ($row['strength'] ?? '')) ?: null,
                'pack_size'               => trim((string) ($row['pack_size'] ?? '')) ?: null,
                'unit_of_measure'         => trim((string) ($row['unit_of_measure'] ?? '')) ?: null,
                'storage_condition'       => trim((string) ($row['storage_condition'] ?? '')) ?: null,
                'buying_price'            => (float) $buyPrice,
                'selling_price'           => (float) $sellPrice,
                'reorder_point'           => (int) ($row['reorder_point'] ?? 0),
                'reorder_qty'             => (int) ($row['reorder_qty'] ?? 0),
                'requires_batch_tracking' => true,
                'tax_code'                => 'EX',
                'tax_id'                  => $ex['id'],
                'tax_percentage'          => $ex['percent'],
                'tax_amount'              => 0.00,
                'sales_amount_with_tax'   => round((float) $sellPrice, 2),
                'hs_code'                 => '00000000',
            ];

            $existing = Stock::where('product_code', $productCode)->first();

            if ($existing) {
                $existing->update($attributes);
                StockAuditLog::record(
                    action: StockAuditLog::IMPORT,
                    productCode: $productCode,
                    productDescription: $productDesc,
                    qtyBefore: $existing->quantity,
                    qtyAfter: $existing->quantity,
                    notes: 'Bulk import — product catalogue updated'
                );
                $this->updated++;
            } else {
                $attributes['product_code'] = $productCode;
                $attributes['quantity']     = 0;
                Stock::create($attributes);
                StockAuditLog::record(
                    action: StockAuditLog::IMPORT,
                    productCode: $productCode,
                    productDescription: $productDesc,
                    qtyBefore: 0,
                    qtyAfter: 0,
                    notes: 'Bulk import — new product'
                );
                $this->imported++;
            }
        }
    }

    public function getResults(): array
    {
        return [
            'imported' => $this->imported,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'errors'   => $this->errors,
        ];
    }
}
