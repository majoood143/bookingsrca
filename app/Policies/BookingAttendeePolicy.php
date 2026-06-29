<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BookingAttendee;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingAttendeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BookingAttendee');
    }

    public function view(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('View:BookingAttendee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BookingAttendee');
    }

    public function update(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('Update:BookingAttendee');
    }

    public function delete(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('Delete:BookingAttendee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BookingAttendee');
    }

    public function restore(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('Restore:BookingAttendee');
    }

    public function forceDelete(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('ForceDelete:BookingAttendee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BookingAttendee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BookingAttendee');
    }

    public function replicate(AuthUser $authUser, BookingAttendee $bookingAttendee): bool
    {
        return $authUser->can('Replicate:BookingAttendee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BookingAttendee');
    }

}