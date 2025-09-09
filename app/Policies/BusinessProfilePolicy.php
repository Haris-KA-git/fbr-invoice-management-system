<?php

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusinessProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view business profiles');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BusinessProfile $businessProfile): bool
    {
        // Admins can view all business profiles
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Check if user owns the business profile
        if ($user->id === $businessProfile->user_id) {
            return true;
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($businessProfile->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins can always create business profiles
        if ($user->hasRole('Admin')) {
            return $user->can('create business profiles');
        }

        return $user->can('create business profiles') && $user->canCreateBusinessProfile();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BusinessProfile $businessProfile): bool
    {
        // Admins can update all business profiles
        if ($user->hasRole('Admin')) {
            return $user->can('edit business profiles');
        }

        // Only owners can update business profiles
        return $user->id === $businessProfile->user_id && $user->can('edit business profiles');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BusinessProfile $businessProfile): bool
    {
        // Admins can delete any business profile
        if ($user->hasRole('Admin')) {
            return $user->can('delete business profiles');
        }

        // Only owners can delete business profiles
        return $user->id === $businessProfile->user_id && $user->can('delete business profiles');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BusinessProfile $businessProfile): bool
    {
        return $user->id === $businessProfile->user_id || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BusinessProfile $businessProfile): bool
    {
        return $user->id === $businessProfile->user_id || $user->hasRole('Admin');
    }
}