<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view customers');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Admins can view all customers
        if ($user->hasRole('Admin')) {
            return $user->can('view customers');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $customer->business_profile_id)->exists()) {
            return $user->can('view customers');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($customer->business_profile_id, 'view_customers') && 
               $user->can('view customers');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create customers');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Admins can update all customers
        if ($user->hasRole('Admin')) {
            return $user->can('edit customers');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $customer->business_profile_id)->exists()) {
            return $user->can('edit customers');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($customer->business_profile_id, 'edit_customers') && 
               $user->can('edit customers');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Admins can delete all customers
        if ($user->hasRole('Admin')) {
            return $user->can('delete customers');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $customer->business_profile_id)->exists()) {
            return $user->can('delete customers');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($customer->business_profile_id, 'edit_customers') && 
               $user->can('delete customers');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        // Admins can restore all customers
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can restore customers
        return $user->businessProfiles()->where('id', $customer->business_profile_id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        // Admins can force delete all customers
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can force delete customers
        return $user->businessProfiles()->where('id', $customer->business_profile_id)->exists();
    }
}