<?php
use App\Http\Controllers\UserLanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/all', [UserLanguageController::class, 'getAllLanguages']);
Route::get('/default', [UserLanguageController::class, 'getUserDefaultLanguage']);
Route::put('/default', [UserLanguageController::class, 'setUserDefaultLanguage']);