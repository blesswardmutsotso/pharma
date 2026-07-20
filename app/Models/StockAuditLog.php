<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAuditLog extends Model
{
    public $timestamps  = false;
    const CREATED_AT    = 'created_at';

    // ── Action constants ──────────────────────────────────────────
    const STOCK_IN      = 'STOCK_IN';
    const MANUAL_EDIT   = 'MANUAL_EDIT';
    const STOCK_DELETE  = 'STOCK_DELETE';
    const SALE          = 'SALE';
    const RETURN_GOODS  = 'RETURN';
    const TRANSFER_OUT  = 'TRANSFER_OUT';
    const TRANSFER_IN   = 'TRANSFER_IN';
    const IMPORT        = 'IMPORT';
    const GRN_DISCREPANCY = 'GRN_DISCREPANCY';
    const ADJUSTMENT    = 'ADJUSTMENT';

    protected $fillable = [
        'product_code', 'product_description', 'action',
        'qty_before', 'qty_after', 'qty_change',
        'reference_type', 'reference_id', 'reference_label',
        'notes', 'performed_by', 'performed_by_name', 'ip_address',
    ];

    protected $casts = ['created_at' => 'datetime'];

    // ── Relationships ─────────────────────────────────────────────
    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ── Static factory — the only way audit entries should be created ──
    public static function record(
        string  $action,
        string  $productCode,
        string  $productDescription,
        int     $qtyBefore,
        int     $qtyAfter,
        string  $notes          = '',
        string  $referenceType  = null,
        int     $referenceId    = null,
        string  $referenceLabel = null
    ): void {
        static::create([
            'product_code'        => $productCode,
            'product_description' => $productDescription,
            'action'              => $action,
            'qty_before'          => $qtyBefore,
            'qty_after'           => $qtyAfter,
            'qty_change'          => $qtyAfter - $qtyBefore,
            'reference_type'      => $referenceType,
            'reference_id'        => $referenceId,
            'reference_label'     => $referenceLabel,
            'notes'               => $notes,
            'performed_by'        => auth()->id(),
            'performed_by_name'   => auth()->user()?->name ?? 'System',
            'ip_address'          => request()->ip(),
        ]);
    }

    // ── Display helpers ───────────────────────────────────────────
    public function actionLabel(): string
    {
        return match ($this->action) {
            self::STOCK_IN     => 'Stock Added',
            self::MANUAL_EDIT  => 'Manual Edit',
            self::STOCK_DELETE => 'Product Deleted',
            self::SALE         => 'Sale',
            self::RETURN_GOODS => 'Return / Credit Note',
            self::TRANSFER_OUT => 'Transfer Out',
            self::TRANSFER_IN  => 'Transfer In',
            self::IMPORT       => 'Import',
            self::GRN_DISCREPANCY => 'GRN Discrepancy',
            self::ADJUSTMENT   => 'Stock Adjustment',
            default            => ucwords(strtolower(str_replace('_', ' ', $this->action))),
        };
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            self::STOCK_IN, self::TRANSFER_IN, self::RETURN_GOODS, self::IMPORT => 'green',
            self::SALE, self::TRANSFER_OUT, self::STOCK_DELETE                  => 'red',
            self::GRN_DISCREPANCY => 'amber',
            self::ADJUSTMENT => 'amber',
            default => 'amber',
        };
    }
}
