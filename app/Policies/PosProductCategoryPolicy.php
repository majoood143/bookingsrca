<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosProductCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosProductCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosProductCategory');
    }

    public function view(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('View:PosProductCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosProductCategory');
    }

    public function update(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('Update:PosProductCategory');
    }

    public function delete(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('Delete:PosProductCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosProductCategory');
    }

    public function restore(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('Restore:PosProductCategory');
    }

    public function forceDelete(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('ForceDelete:PosProductCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosProductCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosProductCategory');
    }

    public function replicate(AuthUser $authUser, PosProductCategory $posProductCategory): bool
    {
        return $authUser->can('Replicate:PosProductCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosProductCategory');
    }

}