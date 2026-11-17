<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ArDestination;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ArDestinationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ArDestination');
    }

    public function view(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('View:ArDestination');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ArDestination');
    }

    public function update(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('Update:ArDestination');
    }

    public function delete(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('Delete:ArDestination');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ArDestination');
    }

    public function restore(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('Restore:ArDestination');
    }

    public function forceDelete(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('ForceDelete:ArDestination');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ArDestination');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ArDestination');
    }

    public function replicate(AuthUser $authUser, ArDestination $arDestination): bool
    {
        return $authUser->can('Replicate:ArDestination');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ArDestination');
    }
}
