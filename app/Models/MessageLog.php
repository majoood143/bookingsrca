<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One WhatsApp or SMS message the app tried to send to a customer, sent or failed. */
class MessageLog extends Model
{
    protected $fillable = [
        'channel',
        'type',
        'recipient',
        'status',
        'message',
        'provider_reference',
        'error',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function succeeded(): bool
    {
        return $this->status === 'sent';
    }
}
