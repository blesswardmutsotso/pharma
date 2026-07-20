<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;

    const STATUS_ACTIVE     = 'active';
    const STATUS_QUARANTINE = 'quarantine';
    const STATUS_EXPIRED    = 'expired';
    const STATUS_DEPLETED   = 'depleted';

    protected $fillable = [
        'product_code',
        'branch_id',
        'batch_number',
        'expiry_date',
        'qty_on_hand',
        'qty_reserved',
        'unit_cost',
        'status',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'expiry_date'  => 'date',
        'qty_on_hand'  => 'integer',
        'qty_reserved' => 'integer',
        'unit_cost'    => 'decimal:2',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'product_code', 'product_code');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemAllocations()
    {
        return $this->hasMany(SalesOrderItemBatch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to a specific branch. A null $branchId leaves the query
     * unscoped (legacy/single-location behaviour is unaffected).
     */
    public function scopeAtBranch($query, ?int $branchId)
    {
        return $branchId === null ? $query : $query->where('branch_id', $branchId);
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->copy()->addDays($days)->toDateString());
    }

    public function scopeOrderedForFefo($query)
    {
        return $query->active()->where('qty_on_hand', '>', 0)->orderBy('expiry_date');
    }

    public function availableQty(): int
    {
        return max($this->qty_on_hand - $this->qty_reserved, 0);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function reserve(int $qty): void
    {
        $this->increment('qty_reserved', $qty);
    }

    public function release(int $qty): void
    {
        $this->update(['qty_reserved' => max($this->qty_reserved - $qty, 0)]);
    }

    /**
     * Physically ship reserved stock out of this batch — reduces both
     * on-hand and reserved quantities, marking the batch depleted once empty.
     */
    public function dispatch(int $qty): void
    {
        $this->qty_on_hand  = max($this->qty_on_hand - $qty, 0);
        $this->qty_reserved = max($this->qty_reserved - $qty, 0);
        if ($this->qty_on_hand === 0) {
            $this->status = self::STATUS_DEPLETED;
        }
        $this->save();
    }
}
