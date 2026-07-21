<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventFieldVisibilitySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',

        'event_booking_override_enabled',
        'event_booking_show_email',
        'event_booking_require_email',
        'event_booking_show_phone',
        'event_booking_require_phone',
        'event_booking_show_date_of_birth',
        'event_booking_require_date_of_birth',
        'event_booking_show_gender',
        'event_booking_require_gender',
        'event_booking_show_nationality',
        'event_booking_require_nationality',
        'event_booking_show_identity_number',
        'event_booking_require_identity_number',
        'event_booking_show_passport_number',
        'event_booking_require_passport_number',
        'event_booking_show_identity_card_upload',
        'event_booking_require_identity_card_upload',
        'event_booking_show_passport_upload',
        'event_booking_require_passport_upload',

        'kiosk_override_enabled',
        'kiosk_show_email',
        'kiosk_require_email',
        'kiosk_show_phone',
        'kiosk_require_phone',
        'kiosk_show_date_of_birth',
        'kiosk_require_date_of_birth',
        'kiosk_show_gender',
        'kiosk_require_gender',
        'kiosk_show_nationality',
        'kiosk_require_nationality',
        'kiosk_show_identity_number',
        'kiosk_require_identity_number',
        'kiosk_show_passport_number',
        'kiosk_require_passport_number',
        'kiosk_show_identity_card_upload',
        'kiosk_require_identity_card_upload',
        'kiosk_show_passport_upload',
        'kiosk_require_passport_upload',
    ];

    protected $casts = [
        'event_booking_override_enabled'       => 'boolean',
        'event_booking_show_email'             => 'boolean',
        'event_booking_require_email'          => 'boolean',
        'event_booking_show_phone'             => 'boolean',
        'event_booking_require_phone'          => 'boolean',
        'event_booking_show_date_of_birth'     => 'boolean',
        'event_booking_require_date_of_birth'  => 'boolean',
        'event_booking_show_gender'            => 'boolean',
        'event_booking_require_gender'         => 'boolean',
        'event_booking_show_nationality'       => 'boolean',
        'event_booking_require_nationality'    => 'boolean',
        'event_booking_show_identity_number'   => 'boolean',
        'event_booking_require_identity_number' => 'boolean',
        'event_booking_show_passport_number'   => 'boolean',
        'event_booking_require_passport_number' => 'boolean',
        'event_booking_show_identity_card_upload'   => 'boolean',
        'event_booking_require_identity_card_upload' => 'boolean',
        'event_booking_show_passport_upload'   => 'boolean',
        'event_booking_require_passport_upload' => 'boolean',

        'kiosk_override_enabled'       => 'boolean',
        'kiosk_show_email'             => 'boolean',
        'kiosk_require_email'          => 'boolean',
        'kiosk_show_phone'             => 'boolean',
        'kiosk_require_phone'          => 'boolean',
        'kiosk_show_date_of_birth'     => 'boolean',
        'kiosk_require_date_of_birth'  => 'boolean',
        'kiosk_show_gender'            => 'boolean',
        'kiosk_require_gender'         => 'boolean',
        'kiosk_show_nationality'       => 'boolean',
        'kiosk_require_nationality'    => 'boolean',
        'kiosk_show_identity_number'   => 'boolean',
        'kiosk_require_identity_number' => 'boolean',
        'kiosk_show_passport_number'   => 'boolean',
        'kiosk_require_passport_number' => 'boolean',
        'kiosk_show_identity_card_upload'   => 'boolean',
        'kiosk_require_identity_card_upload' => 'boolean',
        'kiosk_show_passport_upload'   => 'boolean',
        'kiosk_require_passport_upload' => 'boolean',
    ];

    // The attendee fields governed by this setting, in display order.
    public const FIELDS = [
        'email', 'phone', 'date_of_birth', 'gender', 'nationality', 'identity_number',
        'passport_number', 'identity_card_upload', 'passport_upload',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Resolves this record's overrides for a given scope ("event_booking" or "kiosk")
    // into a flat [show_x => bool, require_x => bool] map, or null when the override
    // isn't enabled for that scope — the caller should fall back to global settings.
    public function overridesFor(string $scope): ?array
    {
        if (! $this->{"{$scope}_override_enabled"}) {
            return null;
        }

        $overrides = [];

        foreach (self::FIELDS as $field) {
            $overrides["show_{$field}"] = (bool) $this->{"{$scope}_show_{$field}"};
            $overrides["require_{$field}"] = (bool) $this->{"{$scope}_require_{$field}"};
        }

        return $overrides;
    }
}
