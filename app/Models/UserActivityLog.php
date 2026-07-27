<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const FAILED_LOGIN = 'failed_login';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::LOGIN => 'Logged In',
            self::LOGOUT => 'Logged Out',
            self::FAILED_LOGIN => 'Failed Login Attempt',
            default => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            self::LOGIN => 'green',
            self::LOGOUT => 'amber',
            self::FAILED_LOGIN => 'red',
            default => 'amber',
        };
    }
}
