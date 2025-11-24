<?php
use App\Http\Controllers\FormController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FormController::class, 'index']);
Route::post('/', [FormController::class, 'store']);
Route::get('/{id}', [FormController::class, 'show']);
Route::put('/{id}', [FormController::class, 'update']);
Route::post('/{id}/archive', [FormController::class, 'archive']);
Route::post('/{id}/restore', [FormController::class, 'restore']);