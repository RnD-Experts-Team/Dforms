<?php
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CategoryController::class, 'index']);
Route::post('/', [CategoryController::class, 'store']);
Route::get('/{id}', [CategoryController::class, 'show']);
Route::put('/{id}', [CategoryController::class, 'update']);
Route::delete('/{id}', [CategoryController::class, 'destroy']);
Route::post('/assign-forms', [CategoryController::class, 'assignForms']);
Route::post('/unassign-forms', [CategoryController::class, 'unassignForms']);