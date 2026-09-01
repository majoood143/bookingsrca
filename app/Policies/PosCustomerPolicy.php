<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PosCustomer;
use Illuminate\Auth\Access\HandlesAuthorization;

class PosCustomerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosCustomer');
    }

    public function view(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('View:PosCustomer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosCustomer');
    }

    public function update(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('Update:PosCustomer');
    }

    public function delete(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('Delete:PosCustomer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PosCustomer');
    }

    public function restore(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('Restore:PosCustomer');
    }

    public function forceDelete(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('ForceDelete:PosCustomer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosCustomer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosCustomer');
    }

    public function replicate(AuthUser $authUser, PosCustomer $posCustomer): bool
    {
        return $authUser->can('Replicate:PosCustomer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosCustomer');
    }

}