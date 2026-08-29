<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosDiscount;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosDiscountPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosDiscount');
    }

    public function view(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('View:PosDiscount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosDiscount');
    }

    public function update(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('Update:PosDiscount');
    }

    public function delete(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('Delete:PosDiscount');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosDiscount');
    }

    public function restore(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('Restore:PosDiscount');
    }

    public function forceDelete(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('ForceDelete:PosDiscount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosDiscount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosDiscount');
    }

    public function replicate(AuthUser $authUser, PosDiscount $posDiscount): bool
    {
        return $authUser->can('Replicate:PosDiscount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosDiscount');
    }

}