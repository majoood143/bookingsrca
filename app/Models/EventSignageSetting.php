<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class EventSignageSetting extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'event_id',
        'logo_path',
        'background_image_path',
        'qr_code_image_path',
        'contact_phone',
        'meeting_point',
        'welcome_message',
        'early_arrival_minutes',
        'gathering_alert_minutes',
        'ready_threshold_minutes',
        'soon_threshold_minutes',
        'upcoming_trips_count',
        'language_switch_seconds',
        'is_enabled',
    ];

    protected $translatable = ['meeting_point', 'welcome_message'];

    protected $casts = [
        'early_arrival_minutes' => 'integer',
        'gathering_alert_minutes' => 'integer',
        'ready_threshold_minutes' => 'integer',
        'soon_threshold_minutes' => 'integer',
        'upcoming_trips_count' => 'integer',
        'language_switch_seconds' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
