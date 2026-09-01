<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    /**
     * Determine whether the user can view any equipment (within their own company).
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the equipment.
     */
    public function view(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id;
    }

    /**
     * Determine whether the user can create equipment.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('create equipment');
    }

    /**
     * Determine whether the user can update the equipment.
     */
    public function update(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id
            && ($user->isAdmin() || $user->hasPermissionTo('update equipment'));
    }

    /**
     * Determine whether the user can delete the equipment.
     */
    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id
            && ($user->isAdmin() || $user->hasPermissionTo('delete equipment'));
    }

    /**
     * Determine whether the user can manage equipment maintenance.
     */
    public function manageMaintenance(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id
            && ($user->isAdmin() || $user->hasPermissionTo('manage maintenance'));
    }
}
