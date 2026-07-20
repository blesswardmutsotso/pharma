<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_code',
        'product_description',
        'qty_ordered',
        'qty_allocated',
        'qty_dispatched',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'qty_ordered'    => 'integer',
        'qty_allocated'  => 'integer',
        'qty_dispatched' => 'integer',
        'unit_price'     => 'decimal:2',
        'line_total'     => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function batchAllocations()
    {
        return $this->hasMany(SalesOrderItemBatch::class);
    }

    public function isFullyAllocated(): bool
    {
        return $this->qty_allocated >= $this->qty_ordered;
    }
}
