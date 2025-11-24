<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActionController;

Route::get('/', [ActionController::class, 'index']);
Route::post('/', [ActionController::class, 'store']);
Route::get('/{id}', [ActionController::class, 'show']);
Route::put('/{id}', [ActionController::class, 'update']);
Route::delete('/{id}', [ActionController::class, 'destroy']);