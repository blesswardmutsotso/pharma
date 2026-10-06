<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCreditNote extends Model
{
    use HasFactory;

    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN     = 'return';

    protected $fillable = [
        'credit_note_number',
        'sales_invoice_id',
        'sales_order_id',
        'amount',
        'reason',
        'type',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesCreditNoteItem::class);
    }

    public function isGoodsReturn(): bool
    {
        return $this->type === self::TYPE_RETURN;
    }

    public function amountInUsd(): float
    {
        return ExchangeRate::toUsd((float) $this->amount, $this->salesInvoice?->currency() ?? 'USD');
    }

    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-' . now()->format('Ymd') . '-';
        $todayCount = static::where('credit_note_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }
}
