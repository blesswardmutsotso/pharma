<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCreditNoteItem extends Model
{
    protected $fillable = [
        'sales_credit_note_id',
        'sales_invoice_item_id',
        'product_code',
        'product_description',
        'batch_number',
        'expiry_date',
        'qty',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'line_total',
        'stock_batch_id',
    ];

    protected $casts = [
        'expiry_date'    => 'date',
        'qty'            => 'integer',
        'unit_price'     => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'line_total'     => 'decimal:2',
    ];

    public function creditNote()
    {
        return $this->belongsTo(SalesCreditNote::class, 'sales_credit_note_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class, 'sales_invoice_item_id');
    }

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class);
    }
}
