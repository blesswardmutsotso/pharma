<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_id',
        'product_code',
        'product_description',
        'batch_number',
        'expiry_date',
        'qty',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'expiry_date'    => 'date',
        'qty'            => 'integer',
        'unit_price'     => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'line_total'     => 'decimal:2',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
