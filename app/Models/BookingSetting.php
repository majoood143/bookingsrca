<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BookingSetting extends Model
{
    use LogsActivity;

    protected $fillable = ['key', 'value', 'type', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value', 'type', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get a setting value by key with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("booking_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'number' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                default => $setting->value,
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("booking_setting_{$key}");
    }

    /**
     * The currency symbol/code to display alongside amounts (e.g. "OMR").
     */
    public static function currencySymbol(): string
    {
        return (string) self::get('currency_symbol', 'OMR');
    }

    /**
     * Base64 data URI of the uploaded currency SVG icon, or null if none is
     * configured. Embedding as a data URI (rather than linking the storage
     * URL) means it renders the same whether the caller is a browser page,
     * an email client, or a PDF engine (mPDF/browsershot) with no network
     * access back to this app.
     */
    public static function currencyIconDataUri(): ?string
    {
        $path = self::get('currency_icon');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return 'data:image/svg+xml;base64,' . base64_encode(Storage::disk('public')->get($path));
    }

    /**
     * The gateways enabled for online checkout, falling back to the legacy
     * single active_gateway value for installs that haven't saved the
     * payment gateways settings page since the multi-gateway option was
     * introduced. Shared by every online-payment flow (event booking, POS
     * shop checkout) so they all read the same admin-configured gateway list.
     */
    public static function enabledGateways(): array
    {
        $stored = self::get('enabled_gateways');
        $decoded = $stored ? json_decode($stored, true) : null;

        if (is_array($decoded) && !empty($decoded)) {
            return array_values($decoded);
        }

        return [(string) self::get('active_gateway', 'free')];
    }

    public static function clearCache(): void
    {
        foreach (self::getCacheKeys() as $key) {
            Cache::forget("booking_setting_{$key}");
        }
    }

    private static function getCacheKeys(): array
    {
        return [
            'max_tickets_per_booking',
            'min_tickets_per_booking',
            'show_email',
            'show_phone',
            'show_date_of_birth',
            'show_gender',
            'show_nationality',
            'show_identity_number',
            'show_slot_end_time',
            'checkin_date_restriction_enabled',
            'checkin_date_restriction_mode',
            'checkin_date_grace_days',
            'max_attendee_age_years',
            'terms_en',
            'terms_ar',
            'terms_url_en',
            'terms_url_ar',
            'enabled_gateways',
            'pending_booking_expiry_minutes',
            'site_name_en',
            'site_name_ar',
            'site_logo',
            'primary_color',
            'secondary_color',
            'timezone',
            'currency_code',
            'currency_symbol',
            'currency_icon',
            'app_logo',
            'favicon',
            'panel_primary_color',
            'module_kiosk_enabled',
            'module_extra_services_enabled',
            'module_private_events_enabled',
            'module_promo_codes_enabled',
            'module_expenses_enabled',
            'module_commission_enabled',
            'module_cms_enabled',
            'module_pos_enabled',
            'module_pos_shop_enabled',
            'pos_vat_registration_number',
            'pos_discount_approval_threshold',
            'pos_shift_variance_tolerance',
            'social_facebook_url',
            'social_instagram_url',
            'social_x_url',
            'social_linkedin_url',
            'social_youtube_url',
            'social_tiktok_url',
            'social_whatsapp_url',
            'seo_meta_description',
            'seo_og_image',
            'success_page_back_url',
            'success_page_message_en',
            'success_page_message_ar',
            'success_page_message_color',
        ];
    }
}
