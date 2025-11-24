<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslationController;

Route::get('/available-languages', [TranslationController::class, 'getAvailableLanguages']);
Route::get('/localizable-data', [TranslationController::class, 'getLocalizableData']);
Route::post('/save', [TranslationController::class, 'saveTranslations']);