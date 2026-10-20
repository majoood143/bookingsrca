<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MessageLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessageLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MessageLog');
    }

    public function view(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('View:MessageLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MessageLog');
    }

    public function update(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('Update:MessageLog');
    }

    public function delete(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('Delete:MessageLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MessageLog');
    }

    public function restore(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('Restore:MessageLog');
    }

    public function forceDelete(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('ForceDelete:MessageLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MessageLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MessageLog');
    }

    public function replicate(AuthUser $authUser, MessageLog $messageLog): bool
    {
        return $authUser->can('Replicate:MessageLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MessageLog');
    }

}