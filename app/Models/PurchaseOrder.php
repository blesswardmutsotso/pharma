<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    const STATUS_DRAFT     = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED  = 'approved';
    const STATUS_RECEIVED  = 'received';
    const STATUS_CLOSED    = 'closed';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'notes',
        'requested_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $todayCount = static::where('po_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canBeClosed(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_RECEIVED], true);
    }

    public function submit(): void
    {
        $this->update(['status' => self::STATUS_SUBMITTED]);
    }

    public function approve(User $approver): void
    {
        $this->update(['status' => self::STATUS_APPROVED, 'approved_by' => $approver->id]);
    }

    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Recalculate this PO's status from its items' received quantities.
     * Called after every GRN posting against this PO.
     */
    public function refreshReceivedStatus(): void
    {
        if (!in_array($this->status, [self::STATUS_APPROVED, self::STATUS_RECEIVED], true)) {
            return;
        }

        $fullyReceived = $this->items()->whereColumn('qty_received', '<', 'qty_ordered')->doesntExist();

        if ($fullyReceived && $this->status !== self::STATUS_RECEIVED) {
            $this->update(['status' => self::STATUS_RECEIVED]);
        }
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED  => 'badge-approved',
            self::STATUS_SUBMITTED => 'badge-pending',
            self::STATUS_RECEIVED  => 'badge-approved',
            self::STATUS_CLOSED    => 'badge-cancelled',
            default                => 'badge-draft',
        };
    }
}
