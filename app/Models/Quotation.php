<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    const STATUS_DRAFT     = 'draft';
    const STATUS_SENT      = 'sent';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'quote_number',
        'client_id',
        'quote_date',
        'valid_until',
        'status',
        'notes',
        'created_by',
        'converted_sales_order_id',
    ];

    protected $casts = [
        'quote_date'  => 'date',
        'valid_until' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedSalesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'converted_sales_order_id');
    }

    public static function generateQuoteNumber(): string
    {
        $prefix = 'QUO-' . now()->format('Ymd') . '-';
        $todayCount = static::where('quote_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    public function canBeConverted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_ACCEPTED], true);
    }

    public function markConverted(SalesOrder $salesOrder): void
    {
        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_sales_order_id' => $salesOrder->id,
        ]);
    }
}
