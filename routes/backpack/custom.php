<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('incident', 'IncidentCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('location', 'LocationCrudController');

    Route::get('recherche', [
    \App\Http\Controllers\Admin\IncidentSearchController::class,
    'index',
])->name('c3i.incidents.search');

    Route::get('recherche/export-csv', [
    \App\Http\Controllers\Admin\IncidentSearchController::class,
    'exportCsv',
])->name('c3i.incidents.export.csv');

    Route::get('carte', [
    \App\Http\Controllers\Admin\IncidentMapController::class,
    'index',
])->name('c3i.incidents.map');

    Route::get('graphiques', [
    \App\Http\Controllers\Admin\IncidentChartsController::class,
    'index',
])->name('c3i.incidents.charts');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
