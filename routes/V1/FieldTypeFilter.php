<?php
use App\Http\Controllers\FieldTypeFilterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FieldTypeFilterController::class, 'index']);
Route::post('/', [FieldTypeFilterController::class, 'store']);
Route::get('/{id}', [FieldTypeFilterController::class, 'show']);
Route::put('/{id}', [FieldTypeFilterController::class, 'update']);
Route::delete('/{id}', [FieldTypeFilterController::class, 'destroy']);