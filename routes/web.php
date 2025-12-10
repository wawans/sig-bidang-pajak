<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Account\GeneralAccountController;
use App\Http\Controllers\Account\PasswordAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\LayerGroupController;
use App\Http\Controllers\MapProviderController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Permission\RoleController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Impersonate;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/general', [GeneralAccountController::class, 'index'])->name('general.index');
        Route::post('/general', [GeneralAccountController::class, 'store'])->name('general.store');

        Route::get('/password', [PasswordAccountController::class, 'index'])->name('password.index');
        Route::post('/password', [PasswordAccountController::class, 'store'])->name('password.store');
    });

    Route::prefix('setting')->group(function () {

        Route::get('user/export', [UserController::class, 'export'])->name('user.export');
        Route::get('role/export', [RoleController::class, 'export'])->name('role.export');
        Route::get('permission/export', [PermissionController::class, 'export'])->name('permission.export');
        Route::resource('user', UserController::class)->names('user');
        Route::resource('role', RoleController::class)->names('role');
        Route::resource('permission', PermissionController::class)->names('permission');

        Route::prefix('user')->middleware(Impersonate::class)->group(function () {
            Route::impersonate();
        });

        Route::prefix('map')->group(function () {
            Route::resource('layer', LayerController::class)->names('layer');
            Route::resource('layer-group', LayerGroupController::class)->names('layer-group');
            Route::resource('style', StyleController::class)->names('style');
            Route::post('provider/{provider}', [MapProviderController::class, 'store'])->name('provider.store');
            Route::get('provider', [MapProviderController::class, 'index'])->name('provider.index');
        });

        Route::name('setting.')->group(function () {
            //
        });

        Route::get('/', [IndexSettingController::class, '__invoke'])->name('setting.index');
    });

    Route::get('editor', [MapController::class, 'edit'])->name('map.editor');
    Route::get('map', [MapController::class, 'index'])->name('map.viewer');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});


require __DIR__.'/auth.php';
