<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_number',
        'sales_invoice_id',
        'amount',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-' . now()->format('Ymd') . '-';
        $todayCount = static::where('credit_note_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }
}
