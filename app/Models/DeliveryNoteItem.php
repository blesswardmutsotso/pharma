<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'product_code',
        'product_description',
        'batch_number',
        'expiry_date',
        'qty',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty' => 'integer',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
