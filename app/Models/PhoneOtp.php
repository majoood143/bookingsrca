<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A one-time code sent to a phone; only a keyed hash of the code is kept. */
class PhoneOtp extends Model
{
    protected $fillable = [
        'phone',
        'purpose',
        'code_hash',
        'attempts',
        'channel',
        'expires_at',
        'consumed_at',
        'ip',
    ];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'attempts' => 'integer',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];
}
