<?php
use App\Http\Controllers\InputRuleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InputRuleController::class, 'index']);
Route::post('/', [InputRuleController::class, 'store']);
Route::get('/{id}', [InputRuleController::class, 'show']);
Route::put('/{id}', [InputRuleController::class, 'update']);
Route::delete('/{id}', [InputRuleController::class, 'destroy']);