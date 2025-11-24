<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;

Route::get('/', [LanguageController::class, 'index']);
Route::post('/', [LanguageController::class, 'store']);
Route::put('/{id}', [LanguageController::class, 'update']);
Route::delete('/{id}', [LanguageController::class, 'destroy']);
Route::post('/set-default', [LanguageController::class, 'setDefault']);
