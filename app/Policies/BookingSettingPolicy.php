<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BookingSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BookingSetting');
    }

    public function view(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('View:BookingSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BookingSetting');
    }

    public function update(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('Update:BookingSetting');
    }

    public function delete(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('Delete:BookingSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BookingSetting');
    }

    public function restore(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('Restore:BookingSetting');
    }

    public function forceDelete(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('ForceDelete:BookingSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BookingSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BookingSetting');
    }

    public function replicate(AuthUser $authUser, BookingSetting $bookingSetting): bool
    {
        return $authUser->can('Replicate:BookingSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BookingSetting');
    }

}