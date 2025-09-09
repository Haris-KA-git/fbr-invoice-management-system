<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\User;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register audit observers
        BusinessProfile::observe(AuditObserver::class);
        Customer::observe(AuditObserver::class);
        Item::observe(AuditObserver::class);
        Invoice::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }
}