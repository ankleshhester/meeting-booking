<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeeCostMaster;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeCostMasterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeeCostMaster');
    }

    public function view(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('View:EmployeeCostMaster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeeCostMaster');
    }

    public function update(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('Update:EmployeeCostMaster');
    }

    public function delete(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('Delete:EmployeeCostMaster');
    }

    public function restore(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('Restore:EmployeeCostMaster');
    }

    public function forceDelete(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('ForceDelete:EmployeeCostMaster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeeCostMaster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeeCostMaster');
    }

    public function replicate(AuthUser $authUser, EmployeeCostMaster $employeeCostMaster): bool
    {
        return $authUser->can('Replicate:EmployeeCostMaster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeeCostMaster');
    }

}