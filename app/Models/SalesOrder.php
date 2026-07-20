<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    const STATUS_DRAFT      = 'draft';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_PICKING    = 'picking';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_INVOICED   = 'invoiced';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'so_number',
        'client_id',
        'branch_id',
        'quotation_id',
        'order_date',
        'required_date',
        'status',
        'fefo_override',
        'notes',
        'created_by',
        'confirmed_by',
        'dispatched_at',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'required_date' => 'date',
        'fefo_override' => 'boolean',
        'dispatched_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function invoice()
    {
        return $this->hasOne(SalesInvoice::class);
    }

    public static function generateSoNumber(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';
        $todayCount = static::where('so_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canStartPicking(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canBeDispatched(): bool
    {
        return $this->status === self::STATUS_PICKING;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CONFIRMED, self::STATUS_PICKING], true);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED, self::STATUS_DISPATCHED, self::STATUS_INVOICED, self::STATUS_COMPLETED => 'badge-approved',
            self::STATUS_PICKING  => 'badge-pending',
            self::STATUS_CANCELLED => 'badge-cancelled',
            default => 'badge-draft',
        };
    }

    public function markCompletedIfSettled(): void
    {
        if ($this->status === self::STATUS_INVOICED && $this->invoice && $this->invoice->isSettled()) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }
}
