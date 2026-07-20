<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'product_description',
        'buying_price',
        'selling_price',
        'quantity',
        'tax_code',
        'tax_id',
        'tax_percentage',
        'tax_amount',
        'sales_amount_with_tax',
        'hs_code',
        'category',
        'generic_name',
        'dosage_form',
        'strength',
        'pack_size',
        'unit_of_measure',
        'storage_condition',
        'reorder_point',
        'reorder_qty',
        'requires_batch_tracking',
        'default_supplier_id',
        'manufacturer',
        'registration_number',
        'controlled_substance_schedule',
    ];

    protected $casts = [
        'reorder_point'           => 'integer',
        'reorder_qty'             => 'integer',
        'requires_batch_tracking' => 'boolean',
    ];

    public function batches()
    {
        return $this->hasMany(StockBatch::class, 'product_code', 'product_code');
    }

    public function defaultSupplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function isLowStock(): bool
    {
        return $this->reorder_point > 0 && $this->quantity <= $this->reorder_point;
    }

    /**
     * Qty on hand for this product at a specific branch — active batches only.
     * Batches with no branch_id (legacy/unscoped) are excluded here since
     * they don't belong to any single location.
     */
    public function quantityAtBranch(int $branchId): int
    {
        return (int) $this->batches()->active()->where('branch_id', $branchId)->sum('qty_on_hand');
    }

    /**
     * Recalculate the aggregate `quantity` from active batches so the
     * untouched ZIMRA POS flow keeps reading a correct total off this column.
     */
    public function syncQuantityFromBatches(): void
    {
        $this->quantity = (int) $this->batches()->active()->sum('qty_on_hand');
        $this->save();
    }
}
