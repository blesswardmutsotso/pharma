<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_code',
        'product_description',
        'stock_batch_id',
        'batch_number',
        'qty_system',
        'qty_counted',
        'qty_variance',
        'unit_cost',
    ];

    protected $casts = [
        'qty_system'   => 'integer',
        'qty_counted'  => 'integer',
        'qty_variance' => 'integer',
        'unit_cost'    => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class);
    }
}
