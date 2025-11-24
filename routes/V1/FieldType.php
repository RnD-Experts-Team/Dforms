<?php
use App\Http\Controllers\FieldTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FieldTypeController::class, 'index']);
Route::post('/', [FieldTypeController::class, 'store']);
Route::get('/{id}', [FieldTypeController::class, 'show']);
Route::put('/{id}', [FieldTypeController::class, 'update']);
Route::delete('/{id}', [FieldTypeController::class, 'destroy']);