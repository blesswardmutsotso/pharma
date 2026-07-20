<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    const TYPE_STOCK_TAKE = 'stock_take';
    const TYPE_DAMAGE     = 'damage';
    const TYPE_THEFT      = 'theft';
    const TYPE_BREAKAGE   = 'breakage';
    const TYPE_OTHER      = 'other';

    const STATUS_DRAFT     = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';

    protected $fillable = [
        'adjustment_no',
        'branch_id',
        'type',
        'status',
        'reason',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_STOCK_TAKE => 'Stock Take (Cycle Count)',
            self::TYPE_DAMAGE     => 'Damage',
            self::TYPE_THEFT      => 'Theft',
            self::TYPE_BREAKAGE   => 'Breakage',
            self::TYPE_OTHER      => 'Other',
        ];
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateAdjustmentNo(): string
    {
        $prefix = 'ADJ-' . now()->format('Ymd') . '-';
        $todayCount = static::where('adjustment_no', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED], true);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED  => 'badge-approved',
            self::STATUS_SUBMITTED => 'badge-pending',
            self::STATUS_REJECTED  => 'badge-cancelled',
            default                => 'badge-draft',
        };
    }

    public function typeLabel(): string
    {
        return static::types()[$this->type] ?? ucfirst($this->type);
    }
}
