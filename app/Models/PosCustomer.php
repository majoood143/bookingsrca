<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosCustomer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'type',
        'name',
        'business_name',
        'mobile',
        'alternate_contact',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'zip_code',
        'is_walk_in',
    ];

    protected $casts = [
        'is_walk_in' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'name', 'business_name', 'mobile', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(PosOrder::class, 'customer_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'business' && $this->business_name) {
            return $this->business_name;
        }

        return $this->name;
    }

    // The single default counter customer used whenever a sale isn't tied to a
    // real contact — created once, on first use, rather than via a seeder.
    public static function walkIn(): self
    {
        return static::firstOrCreate(
            ['is_walk_in' => true],
            ['type' => 'individual', 'name' => __('pos.customer.walk_in')],
        );
    }

    // Matches a returning guest shopper by email so repeat orders land on the
    // same contact record instead of creating a duplicate every checkout.
    public static function findOrCreateForGuestCheckout(array $data): self
    {
        $customer = static::where('email', $data['email'])->where('is_walk_in', false)->first();

        if ($customer) {
            $customer->update($data);

            return $customer;
        }

        return static::create(array_merge($data, ['type' => 'individual']));
    }
}
