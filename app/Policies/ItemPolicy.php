<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view items');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Item $item): bool
    {
        // Admins can view all items
        if ($user->hasRole('Admin')) {
            return $user->can('view items');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $item->business_profile_id)->exists()) {
            return $user->can('view items');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($item->business_profile_id, 'view_items') && 
               $user->can('view items');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create items');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $item): bool
    {
        // Admins can update all items
        if ($user->hasRole('Admin')) {
            return $user->can('edit items');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $item->business_profile_id)->exists()) {
            return $user->can('edit items');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($item->business_profile_id, 'edit_items') && 
               $user->can('edit items');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $item): bool
    {
        // Admins can delete all items
        if ($user->hasRole('Admin')) {
            return $user->can('delete items');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $item->business_profile_id)->exists()) {
            return $user->can('delete items');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($item->business_profile_id, 'edit_items') && 
               $user->can('delete items');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Item $item): bool
    {
        // Admins can restore all items
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can restore items
        return $user->businessProfiles()->where('id', $item->business_profile_id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Item $item): bool
    {
        // Admins can force delete all items
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can force delete items
        return $user->businessProfiles()->where('id', $item->business_profile_id)->exists();
    }
}