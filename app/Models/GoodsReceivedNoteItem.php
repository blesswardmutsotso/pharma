<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_received_note_id',
        'product_code',
        'product_description',
        'qty_received',
        'unit_cost',
        'batch_number',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'qty_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }
}
