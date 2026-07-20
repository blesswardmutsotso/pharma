<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItemBatch extends Model
{
    protected $fillable = [
        'sales_order_item_id',
        'stock_batch_id',
        'qty_allocated',
    ];

    protected $casts = [
        'qty_allocated' => 'integer',
    ];

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class);
    }
}
