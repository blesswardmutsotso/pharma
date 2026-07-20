<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'tin',
        'license_number',
        'license_expiry_date',
        'accreditation_body',
        'address',
        'payment_terms',
        'status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
    ];

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry_date !== null && $this->license_expiry_date->isPast();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
