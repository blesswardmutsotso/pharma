<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'vat_number', 'tin', 'phone', 'email', 'credit_limit',
        'province', 'city', 'district', 'street', 'house_no',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function fullAddress(): string
    {
        return collect([$this->house_no, $this->street, $this->district, $this->city, $this->province])
            ->filter()
            ->implode(', ');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function salesPayments()
    {
        return $this->hasMany(SalesPayment::class);
    }

    /**
     * Total outstanding balance across unpaid/partially-paid invoices —
     * the same definition used by the debtors ageing report and customer
     * statement, so credit-limit checks stay consistent with those figures.
     */
    public function outstandingBalance(): float
    {
        return round(
            $this->salesInvoices()
                ->whereIn('status', [SalesInvoice::STATUS_UNPAID, SalesInvoice::STATUS_PARTIALLY_PAID])
                ->get()
                ->sum(fn (SalesInvoice $invoice) => $invoice->balance()),
            2
        );
    }

    /**
     * Whether confirming an order worth $additionalAmount would push this
     * client over their credit limit. A null/zero limit means no limit is
     * enforced (existing clients keep today's behaviour by default).
     */
    public function wouldExceedCreditLimit(float $additionalAmount): bool
    {
        if (!$this->credit_limit || (float) $this->credit_limit <= 0) {
            return false;
        }

        return $this->outstandingBalance() + $additionalAmount > (float) $this->credit_limit;
    }
}
