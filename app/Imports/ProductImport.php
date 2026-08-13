<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use App\Models\Supplier;
use Carbon\Carbon;
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

            $buyPrice          = $row['buying_price']  ?? '';
            $sellPrice         = $row['selling_price'] ?? '';
            $initialBatchNum   = trim((string) ($row['initial_batch_number'] ?? ''));
            $initialExpiryRaw  = $row['initial_expiry_date'] ?? '';
            $initialQty        = $row['initial_qty'] ?? '';

            $errs = [];
            if ($productDesc === '')      $errs[] = 'product_description required';
            if (!is_numeric($buyPrice))   $errs[] = 'buying_price must be numeric';
            if (!is_numeric($sellPrice))  $errs[] = 'selling_price must be numeric';

            $initialExpiry = null;
            if ($initialBatchNum !== '') {
                if ($initialQty === '' || !is_numeric($initialQty) || (int) $initialQty < 1) {
                    $errs[] = 'initial_qty must be a positive number when initial_batch_number is set';
                }

                try {
                    $initialExpiry = $this->parseExcelDate($initialExpiryRaw);
                } catch (\Throwable $e) {
                    $initialExpiry = null;
                }

                if ($initialExpiry === null) {
                    $errs[] = 'initial_expiry_date must be a valid date when initial_batch_number is set';
                }
            }

            if (!empty($errs)) {
                $this->skipped++;
                $this->errors[] = "Row {$rowNum} ({$productCode}): " . implode('; ', $errs);
                continue;
            }

            $ex = config('zimra.tax.EX', ['id' => 1, 'percent' => 0.0]);

            $supplierName = trim((string) ($row['default_supplier'] ?? ''));
            $supplierId   = $supplierName !== ''
                ? Supplier::where('name', $supplierName)->value('id')
                : null;

            $attributes = [
                'product_description'     => $productDesc,
                'category'                => trim((string) ($row['category'] ?? '')) ?: null,
                'generic_name'            => trim((string) ($row['generic_name'] ?? '')) ?: null,
                'manufacturer'            => trim((string) ($row['manufacturer'] ?? '')) ?: null,
                'registration_number'     => trim((string) ($row['registration_number'] ?? '')) ?: null,
                'controlled_substance_schedule' => trim((string) ($row['controlled_substance_schedule'] ?? '')) ?: null,
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
                'default_supplier_id'     => $supplierId,
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
                $product = Stock::create($attributes);

                if ($initialBatchNum !== '') {
                    StockBatch::create([
                        'product_code' => $product->product_code,
                        'batch_number' => $initialBatchNum,
                        'expiry_date'  => $initialExpiry,
                        'qty_on_hand'  => (int) $initialQty,
                        'unit_cost'    => is_numeric($row['initial_unit_cost'] ?? null) ? (float) $row['initial_unit_cost'] : (float) $buyPrice,
                        'status'       => StockBatch::STATUS_ACTIVE,
                        'source_type'  => 'ProductImport',
                        'source_id'    => $product->id,
                    ]);

                    $product->syncQuantityFromBatches();
                }

                StockAuditLog::record(
                    action: StockAuditLog::IMPORT,
                    productCode: $productCode,
                    productDescription: $productDesc,
                    qtyBefore: 0,
                    qtyAfter: $product->quantity,
                    notes: $initialBatchNum !== ''
                        ? 'Bulk import — new product with initial batch ' . $initialBatchNum
                        : 'Bulk import — new product'
                );
                $this->imported++;
            }
        }
    }

    /**
     * Excel cells with a date format arrive as serial numbers via ToCollection;
     * plain text cells arrive as strings — accept either.
     */
    private function parseExcelDate($value): ?Carbon
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        return Carbon::parse((string) $value);
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
