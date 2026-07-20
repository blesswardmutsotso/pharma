<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active', 'is_home'];

    protected $casts = ['is_active' => 'boolean', 'is_home' => 'boolean'];

    public static function home(): self
    {
        return static::where('is_home', true)->firstOrFail();
    }

    public static function homeOrNull(): ?self
    {
        return static::where('is_home', true)->first();
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }
}
