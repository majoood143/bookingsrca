<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosTax;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosTaxPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosTax');
    }

    public function view(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('View:PosTax');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosTax');
    }

    public function update(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('Update:PosTax');
    }

    public function delete(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('Delete:PosTax');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosTax');
    }

    public function restore(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('Restore:PosTax');
    }

    public function forceDelete(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('ForceDelete:PosTax');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosTax');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosTax');
    }

    public function replicate(AuthUser $authUser, PosTax $posTax): bool
    {
        return $authUser->can('Replicate:PosTax');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosTax');
    }

}