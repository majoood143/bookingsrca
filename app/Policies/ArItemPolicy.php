<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ArItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ArItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ArItem');
    }

    public function view(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('View:ArItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ArItem');
    }

    public function update(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('Update:ArItem');
    }

    public function delete(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('Delete:ArItem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ArItem');
    }

    public function restore(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('Restore:ArItem');
    }

    public function forceDelete(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('ForceDelete:ArItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ArItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ArItem');
    }

    public function replicate(AuthUser $authUser, ArItem $arItem): bool
    {
        return $authUser->can('Replicate:ArItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ArItem');
    }
}
