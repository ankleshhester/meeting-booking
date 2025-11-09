<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BuildingsArea;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuildingsAreaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BuildingsArea');
    }

    public function view(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('View:BuildingsArea');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BuildingsArea');
    }

    public function update(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('Update:BuildingsArea');
    }

    public function delete(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('Delete:BuildingsArea');
    }

    public function restore(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('Restore:BuildingsArea');
    }

    public function forceDelete(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('ForceDelete:BuildingsArea');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BuildingsArea');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BuildingsArea');
    }

    public function replicate(AuthUser $authUser, BuildingsArea $buildingsArea): bool
    {
        return $authUser->can('Replicate:BuildingsArea');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BuildingsArea');
    }

}