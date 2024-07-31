<?php

use Fahlgrendigital\StatamicFormManager\Http\Controllers;
use Fahlgrendigital\StatamicFormManager\Http\Controllers\API;
use Illuminate\Support\Facades\Route;

Route::prefix('formidable')->group(function () {
    Route::get('dashboard', Controllers\DashboardController::class)->name('formidable.index');

    Route::prefix('api')->group(function () {
        Route::resource('exports', API\ExportsController::class)->only(['index']);
    });

    Route::resource('submissions', Controllers\SubmissionsController::class)->only(['show']);
});
