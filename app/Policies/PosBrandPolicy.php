<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosBrand;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosBrandPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosBrand');
    }

    public function view(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('View:PosBrand');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosBrand');
    }

    public function update(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('Update:PosBrand');
    }

    public function delete(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('Delete:PosBrand');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosBrand');
    }

    public function restore(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('Restore:PosBrand');
    }

    public function forceDelete(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('ForceDelete:PosBrand');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosBrand');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosBrand');
    }

    public function replicate(AuthUser $authUser, PosBrand $posBrand): bool
    {
        return $authUser->can('Replicate:PosBrand');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosBrand');
    }

}