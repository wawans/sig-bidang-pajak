<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

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
        $this->registerEvents();

        if (! Blueprint::hasMacro('userTimestamps')) {
            $this->registerBlueprintMacro();
        }
    }

    protected function registerBlueprintMacro(): void
    {
        Blueprint::macro('userTimestamps', function ($precision = 0) {
            $this->timestamp('created_at', $precision)->nullable()->useCurrent();
            $this->unsignedBigInteger('created_by')->nullable();

            $this->timestamp('updated_at', $precision)->nullable()->useCurrent()->useCurrentOnUpdate();
            $this->unsignedBigInteger('updated_by')->nullable();
        });

        Blueprint::macro('userSoftDeletes', function ($column = 'deleted_at', $precision = 0) {
            $this->timestamp($column, $precision)->nullable();
            $this->unsignedBigInteger('deleted_by')->nullable();
        });

        Blueprint::macro('foreignUserTimestamps', function ($table = 'users', $column = 'id') {
            $this->foreign('created_by')->references($column)->on($table);
            $this->foreign('updated_by')->references($column)->on($table);
        });

        Blueprint::macro('foreignUserSoftDeletes', function ($table = 'users', $column = 'id') {
            $this->foreign('deleted_by')->references($column)->on($table);
        });
    }

    protected function registerEvents(): void
    {
        // When impersonation begins
        Event::listen(function (TakeImpersonation $event) {
            session()->put([
                'password_hash_sanctum' => $event->impersonated->getAuthPassword(),
            ]);
        });

        // When impersonation ends
        Event::listen(function (LeaveImpersonation $event) {
            session()->forget('password_hash_web');
            session()->put([
                'password_hash_sanctum' => $event->impersonator->getAuthPassword(),
            ]);

            // Ensure proper user restoration
            Auth::setUser($event->impersonator);
        });
    }
}
