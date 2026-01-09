<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Meeting;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class MeetingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Meeting');
    }

    public function view(AuthUser $authUser, Meeting $meeting): bool
    {
        // 1️⃣ Admin / permission-based access
        if ($authUser->can('View:Meeting')) {
            return true;
        }

        // 2️⃣ Attendee-based access (EMAIL MATCH)
        return $meeting->addAttendee()
            ->whereRaw('LOWER(attendees.email) = ?', [strtolower($authUser->email)])
            ->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Meeting');
    }

    public function update(AuthUser $authUser, Meeting $meeting): bool
    {
        if (Carbon::parse($meeting->date)->lt(Carbon::today())) {
            return false;
        }

        return $authUser->can('Update:Meeting');
    }

    public function delete(AuthUser $authUser, Meeting $meeting): bool
    {
        return $authUser->can('Delete:Meeting');
    }

    public function restore(AuthUser $authUser, Meeting $meeting): bool
    {
        return $authUser->can('Restore:Meeting');
    }

    public function forceDelete(AuthUser $authUser, Meeting $meeting): bool
    {
        return $authUser->can('ForceDelete:Meeting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Meeting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Meeting');
    }

    public function replicate(AuthUser $authUser, Meeting $meeting): bool
    {
        return $authUser->can('Replicate:Meeting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Meeting');
    }
}
