<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Attendee;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Attendee');
    }

    public function view(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('View:Attendee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Attendee');
    }

    public function update(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('Update:Attendee');
    }

    public function delete(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('Delete:Attendee');
    }

    public function restore(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('Restore:Attendee');
    }

    public function forceDelete(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('ForceDelete:Attendee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Attendee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Attendee');
    }

    public function replicate(AuthUser $authUser, Attendee $attendee): bool
    {
        return $authUser->can('Replicate:Attendee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Attendee');
    }

}