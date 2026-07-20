<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_no', 'transfer_type', 'from_branch_id', 'to_branch_id',
        'status', 'notes', 'reference_doc', 'total_items', 'total_qty',
        'requested_by', 'approved_by', 'reject_reason', 'approved_at',
    ];

    protected $casts = ['approved_at' => 'datetime'];

    // ── Status constants ──────────────────────────────────────────
    const STATUS_DRAFT     = 'DRAFT';
    const STATUS_PENDING   = 'PENDING';
    const STATUS_APPROVED  = 'APPROVED';
    const STATUS_REJECTED  = 'REJECTED';
    const STATUS_CANCELLED = 'CANCELLED';

    const TYPE_OUTGOING = 'OUTGOING';
    const TYPE_INCOMING = 'INCOMING';

    // ── Relationships ─────────────────────────────────────────────
    public function items()
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(StockAuditLog::class, 'reference_id')
            ->where('reference_type', 'StockTransfer');
    }

    // ── Transfer number generator ─────────────────────────────────
    public static function generateTransferNo(): string
    {
        $prefix  = 'TRF-' . now()->format('Ymd') . '-';
        $todayCount = static::where('transfer_no', 'LIKE', $prefix . '%')->count() + 1;
        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED  => 'badge-approved',
            self::STATUS_PENDING   => 'badge-pending',
            self::STATUS_REJECTED  => 'badge-rejected',
            self::STATUS_CANCELLED => 'badge-cancelled',
            default                => 'badge-draft',
        };
    }
}
