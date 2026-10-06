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

    public function creditNoteItems()
    {
        return $this->hasMany(SalesCreditNoteItem::class, 'sales_invoice_item_id');
    }

    /**
     * Units already credited back against this invoice line, across every
     * return recorded so far (possibly several separate transactions).
     */
    public function returnedQty(): int
    {
        return (int) $this->creditNoteItems()->sum('qty');
    }

    /**
     * Units still eligible to be returned — caps cumulative returns at what
     * was actually invoiced on this line, no matter how many separate
     * return transactions it's split across.
     */
    public function returnableQty(): int
    {
        return max($this->qty - $this->returnedQty(), 0);
    }
}
