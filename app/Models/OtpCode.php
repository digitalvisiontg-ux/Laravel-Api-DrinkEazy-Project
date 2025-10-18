<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'email',
        'otp_hash',
        'channel',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function generateForPhone(string $phone, int $length = 6, int $ttlMinutes = 5, $userId = null, $channel = 'sms')
    {
        $otp = str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
        $hash = Hash::make($otp);

        $record = self::create([
            'user_id' => $userId,
            'phone' => $phone,
            'otp_hash' => $hash,
            'channel' => $channel,
            'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
        ]);

        // return both record and plain otp so caller can send it
        return ['record' => $record, 'otp' => $otp];
    }
    public static function generateForEmail(string $email, int $length = 6, int $ttlMinutes = 5, $userId = null, $channel = 'email')
    {
        // Génère un code numérique de $length chiffres
        $otp = str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
        $hash = Hash::make($otp);

        $record = self::create([
            'user_id' => $userId,
            'phone' => null,
            'email' => $email,
            'otp_hash' => $hash,
            'channel' => $channel,
            'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
        ]);

        return ['record' => $record, 'otp' => $otp];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function verify(string $candidate): bool
    {
        if ($this->isExpired()) {
            return false;
        }
        return Hash::check($candidate, $this->otp_hash);
    }
}
