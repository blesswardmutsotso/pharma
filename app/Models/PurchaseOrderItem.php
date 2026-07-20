<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_code',
        'product_description',
        'qty_ordered',
        'qty_received',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'qty_ordered' => 'integer',
        'qty_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function discrepancy(): int
    {
        return $this->qty_received - $this->qty_ordered;
    }

    public function hasDiscrepancy(): bool
    {
        return $this->discrepancy() !== 0;
    }

    public function isFullyReceived(): bool
    {
        return $this->qty_received >= $this->qty_ordered;
    }
}
