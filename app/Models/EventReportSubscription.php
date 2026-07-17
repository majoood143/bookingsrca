<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReportSubscription extends Model
{
    protected $fillable = [
        'event_id',
        'recipients',
        'is_enabled',
        'send_day',
        'send_time',
        'last_sent_at',
    ];

    protected $casts = [
        'recipients'   => 'array',
        'is_enabled'   => 'boolean',
        'send_day'     => 'integer',
        'last_sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
