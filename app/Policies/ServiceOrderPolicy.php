<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    /**
     * Determine whether the user can view any service orders (within their own company).
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the service order.
     */
    public function view(User $user, ServiceOrder $order): bool
    {
        return $user->company_id === $order->company_id;
    }

    /**
     * Determine whether the user can create service orders.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('create services');
    }

    /**
     * Determine whether the user can update the service order.
     */
    public function update(User $user, ServiceOrder $order): bool
    {
        return $user->company_id === $order->company_id
            && ($user->isAdmin() || $user->hasPermissionTo('update services'));
    }

    /**
     * Determine whether the user can delete the service order.
     */
    public function delete(User $user, ServiceOrder $order): bool
    {
        return $user->company_id === $order->company_id
            && ($user->isAdmin() || $user->hasPermissionTo('delete services'));
    }
}
