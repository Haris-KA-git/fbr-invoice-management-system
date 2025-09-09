<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessProfile;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign default role
            $user->assignRole('Cashier');

            // Create default business profile
            $businessProfile = BusinessProfile::create([
                'user_id' => $user->id,
                'business_name' => $user->name . "'s Business",
                'address' => 'Please update your business address',
                'province_code' => '01', // Default to Punjab
                'is_sandbox' => true,
                'is_active' => true,
            ]);

            // Add user as owner of the business profile
            $businessProfile->users()->attach($user->id, [
                'role' => 'owner',
                'permissions' => json_encode([
                    'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
                    'view_customers', 'create_customers', 'edit_customers',
                    'view_items', 'create_items', 'edit_items',
                    'view_reports'
                ]),
                'is_active' => true,
            ]);

            event(new Registered($user));
            Auth::login($user);
        });


        return redirect(RouteServiceProvider::HOME);
    }
}