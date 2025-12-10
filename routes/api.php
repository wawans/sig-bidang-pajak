<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\GeoserverApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\LayerGroupController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Permission\RoleController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\UserController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('map')->group(function () {
        Route::get('init', [MapController::class, 'init'])->name('map.init');
    });

    Route::prefix('setting')->group(function () {
        Route::prefix('map')->group(function () {
            Route::patch('layer/{layer}', [LayerController::class, 'patch'])->name('layer.patch');
            Route::get('layer', [LayerController::class, 'table'])->name('layer.table');
            Route::get('layer-group', [LayerGroupController::class, 'table'])->name('layer-group.table');
            Route::get('style', [StyleController::class, 'table'])->name('style.table');
        });

        Route::get('role', [RoleController::class, 'table'])->name('role.table');
        Route::get('permission', [PermissionController::class, 'table'])->name('permission.table');

        Route::patch('user/{user}', [UserController::class, 'patch'])->name('user.patch');
        Route::get('user', [UserController::class, 'table'])->name('user.table');
    });

    Route::prefix('dashboard')->name('dashboard.')->controller(DashboardController::class)->group(function () {
        Route::get('stats', 'stats')->name('stats');
        Route::get('layers', 'layers')->name('layers');
        Route::get('groups', 'groups')->name('groups');
        Route::get('styles', 'styles')->name('styles');
    });

    Route::prefix('geoserver')->name('geoserver.')->controller(GeoserverApiController::class)->group(function () {
        Route::delete('/', 'store')->middleware('can:MAP-EDITOR-DELETE')->name('destroy');
        Route::put('/', 'store')->middleware('can:MAP-EDITOR-UPDATE')->name('update');
        Route::post('/', 'store')->middleware('can:MAP-EDITOR-CREATE')->name('store');
        Route::get('/feature/{layer}/filter-id/{id}', 'featureId')->name('feature.id');
        Route::get('/feature/{layer}/filter-nop/{id}', 'featureNop')->name('feature.nop');
        Route::get('/describe/{layer}', 'describe')->name('describe');
        Route::get('/feature/{layer}', 'feature')->name('feature');
        Route::get('/layer/{layer}', 'layer')->name('layer');
        Route::get('/', 'index')->name('index');
    });



});
