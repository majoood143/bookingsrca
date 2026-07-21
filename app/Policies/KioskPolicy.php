<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kiosk;
use Illuminate\Auth\Access\HandlesAuthorization;

class KioskPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Kiosk');
    }

    public function view(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('View:Kiosk');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kiosk');
    }

    public function update(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('Update:Kiosk');
    }

    public function delete(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('Delete:Kiosk');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Kiosk');
    }

    public function restore(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('Restore:Kiosk');
    }

    public function forceDelete(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('ForceDelete:Kiosk');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kiosk');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kiosk');
    }

    public function replicate(AuthUser $authUser, Kiosk $kiosk): bool
    {
        return $authUser->can('Replicate:Kiosk');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kiosk');
    }

}