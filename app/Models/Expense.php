<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'user_id',
        'date',
        'description',
        'amount',
        'notes',
        'attachment', 
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function rules()
    {
        return [
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:10240', 
        ];
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount, 2);
    }

   
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }
}
