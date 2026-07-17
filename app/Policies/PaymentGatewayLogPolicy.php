<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PaymentGatewayLog;
use Illuminate\Foundation\Auth\User as AuthUser;

class PaymentGatewayLogPolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, PaymentGatewayLog $paymentGatewayLog): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
