<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'branch_id',
        'received_date',
        'received_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceivedNoteItem::class);
    }

    /**
     * GRNs don't carry their own currency — they inherit it from the
     * purchase order the goods were ordered under, defaulting to USD for
     * GRNs with no linked PO.
     */
    public function currency(): string
    {
        return $this->purchaseOrder?->currency ?? PurchaseOrder::CURRENCY_USD;
    }

    public function grandTotal(): float
    {
        return round($this->items->sum(fn (GoodsReceivedNoteItem $item) => $item->lineTotal()), 2);
    }
}
