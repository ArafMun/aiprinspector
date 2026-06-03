<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public static function deleteByEmail(string $email): void
    {
        self::where('email', $email)->delete();
    }
}
