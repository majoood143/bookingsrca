<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosProduct');
    }

    public function view(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('View:PosProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosProduct');
    }

    public function update(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('Update:PosProduct');
    }

    public function delete(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('Delete:PosProduct');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosProduct');
    }

    public function restore(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('Restore:PosProduct');
    }

    public function forceDelete(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('ForceDelete:PosProduct');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosProduct');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosProduct');
    }

    public function replicate(AuthUser $authUser, PosProduct $posProduct): bool
    {
        return $authUser->can('Replicate:PosProduct');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosProduct');
    }

}