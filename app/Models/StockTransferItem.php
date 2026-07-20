<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'transfer_id', 'product_code', 'product_description',
        'qty_requested', 'qty_approved', 'buying_price', 'selling_price',
        'tax_code', 'notes',
    ];

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'product_code', 'product_code');
    }

    public function effectiveQty(): int
    {
        return $this->qty_approved ?? $this->qty_requested;
    }

    public function transferValue(): float
    {
        return round($this->selling_price * $this->effectiveQty(), 2);
    }
}
