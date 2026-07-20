<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPaymentAllocation extends Model
{
    protected $fillable = [
        'sales_payment_id',
        'sales_invoice_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(SalesPayment::class, 'sales_payment_id');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
