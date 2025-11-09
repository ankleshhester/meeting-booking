<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ConferenceRoom;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConferenceRoomPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConferenceRoom');
    }

    public function view(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('View:ConferenceRoom');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConferenceRoom');
    }

    public function update(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('Update:ConferenceRoom');
    }

    public function delete(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('Delete:ConferenceRoom');
    }

    public function restore(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('Restore:ConferenceRoom');
    }

    public function forceDelete(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('ForceDelete:ConferenceRoom');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConferenceRoom');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConferenceRoom');
    }

    public function replicate(AuthUser $authUser, ConferenceRoom $conferenceRoom): bool
    {
        return $authUser->can('Replicate:ConferenceRoom');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConferenceRoom');
    }

}