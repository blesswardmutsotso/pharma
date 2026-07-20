<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    use HasFactory;

    const STATUS_UNPAID          = 'unpaid';
    const STATUS_PARTIALLY_PAID  = 'partially_paid';
    const STATUS_PAID            = 'paid';
    const STATUS_CANCELLED       = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'client_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_total',
        'total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_total'    => 'decimal:2',
        'total'        => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(SalesCreditNote::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(SalesPaymentAllocation::class);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $todayCount = static::where('invoice_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    public function creditedTotal(): float
    {
        return round((float) $this->creditNotes()->sum('amount'), 2);
    }

    public function paidTotal(): float
    {
        return round((float) $this->paymentAllocations()->sum('amount'), 2);
    }

    public function balance(): float
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 0.0;
        }

        return round($this->total - $this->creditedTotal() - $this->paidTotal(), 2);
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_CANCELLED || $this->balance() <= 0.005;
    }

    /**
     * Recompute status from payments/credits recorded so far. Called after
     * every payment allocation or credit note.
     */
    public function refreshStatus(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $balance = $this->balance();

        if ($balance <= 0.005) {
            $this->update(['status' => self::STATUS_PAID]);
        } elseif ($balance < $this->total) {
            $this->update(['status' => self::STATUS_PARTIALLY_PAID]);
        } else {
            $this->update(['status' => self::STATUS_UNPAID]);
        }
    }

    public function daysOverdue(): int
    {
        if (!$this->due_date || $this->isSettled() || !$this->due_date->isPast()) {
            return 0;
        }

        return (int) $this->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay());
    }

    /**
     * Ageing bucket per BRD FR-PAY-002: current, 30, 60, 90+.
     */
    public function ageingBucket(): string
    {
        $days = $this->daysOverdue();

        return match (true) {
            $days <= 0  => 'current',
            $days <= 30 => '30',
            $days <= 60 => '60',
            default     => '90+',
        };
    }
}
