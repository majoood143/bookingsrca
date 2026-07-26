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
        'signage_video_url',
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

    // Extracts the video ID from a youtube.com/youtu.be URL so the signage
    // screen can loop it, or null if the URL doesn't look like YouTube.
    public function getVideoEmbedId(): ?string
    {
        if (blank($this->signage_video_url)) {
            return null;
        }

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $this->signage_video_url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
