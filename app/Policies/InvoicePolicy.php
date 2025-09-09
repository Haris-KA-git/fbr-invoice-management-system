<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view invoices');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Admins can view all invoices
        if ($user->hasRole('Admin')) {
            return $user->can('view invoices');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $invoice->business_profile_id)->exists()) {
            return $user->can('view invoices');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($invoice->business_profile_id, 'view_invoices') && 
               $user->can('view invoices');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create invoices');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Admins can update all invoices
        if ($user->hasRole('Admin')) {
            return $user->can('edit invoices');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $invoice->business_profile_id)->exists()) {
            return $user->can('edit invoices');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($invoice->business_profile_id, 'edit_invoices') && 
               $user->can('edit invoices');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Admins can delete all invoices
        if ($user->hasRole('Admin')) {
            return $user->can('delete invoices');
        }

        // Check if user owns the business profile
        if ($user->businessProfiles()->where('id', $invoice->business_profile_id)->exists()) {
            return $user->can('delete invoices');
        }

        // Check if user has shared access to the business profile
        return $user->hasBusinessProfileAccess($invoice->business_profile_id, 'delete_invoices') && 
               $user->can('delete invoices');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        // Admins can restore all invoices
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can restore invoices
        return $user->businessProfiles()->where('id', $invoice->business_profile_id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // Admins can force delete all invoices
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only owners can force delete invoices
        return $user->businessProfiles()->where('id', $invoice->business_profile_id)->exists();
    }
}