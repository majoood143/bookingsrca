<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KioskCard;
use Illuminate\Auth\Access\HandlesAuthorization;

class KioskCardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KioskCard');
    }

    public function view(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('View:KioskCard');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KioskCard');
    }

    public function update(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('Update:KioskCard');
    }

    public function delete(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('Delete:KioskCard');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KioskCard');
    }

    public function restore(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('Restore:KioskCard');
    }

    public function forceDelete(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('ForceDelete:KioskCard');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KioskCard');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KioskCard');
    }

    public function replicate(AuthUser $authUser, KioskCard $kioskCard): bool
    {
        return $authUser->can('Replicate:KioskCard');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KioskCard');
    }

}