<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormVersionController;

Route::prefix('forms/{formId}/versions')->group(function () {
    Route::get('/', [FormVersionController::class, 'index']);
    Route::post('/', [FormVersionController::class, 'store']);
});

Route::prefix('form-versions')->group(function () {
    Route::get('/{id}', [FormVersionController::class, 'show']);
    Route::put('/{id}', [FormVersionController::class, 'update']);
    Route::post('/{id}/publish', [FormVersionController::class, 'publish']);
});
