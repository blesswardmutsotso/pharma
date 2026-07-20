<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_code',
        'product_description',
        'qty',
        'unit_price',
        'discount',
        'line_total',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'unit_price' => 'decimal:2',
        'discount'   => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
