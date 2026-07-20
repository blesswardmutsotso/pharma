<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPayment extends Model
{
    use HasFactory;

    const METHOD_EFT          = 'eft';
    const METHOD_CASH         = 'cash';
    const METHOD_CHEQUE       = 'cheque';
    const METHOD_MOBILE_MONEY = 'mobile_money';

    protected $fillable = [
        'client_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function allocations()
    {
        return $this->hasMany(SalesPaymentAllocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocatedTotal(): float
    {
        return round((float) $this->allocations()->sum('amount'), 2);
    }

    public function unallocatedTotal(): float
    {
        return round($this->amount - $this->allocatedTotal(), 2);
    }
}
