<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GamePlayer;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePlayerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GamePlayer');
    }

    public function view(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('View:GamePlayer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GamePlayer');
    }

    public function update(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('Update:GamePlayer');
    }

    public function delete(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('Delete:GamePlayer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GamePlayer');
    }

    public function restore(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('Restore:GamePlayer');
    }

    public function forceDelete(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('ForceDelete:GamePlayer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GamePlayer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GamePlayer');
    }

    public function replicate(AuthUser $authUser, GamePlayer $gamePlayer): bool
    {
        return $authUser->can('Replicate:GamePlayer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GamePlayer');
    }

}