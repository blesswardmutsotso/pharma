<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'vat_number', 'tin', 'phone', 'email',
        'province', 'city', 'district', 'street', 'house_no',
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
}
