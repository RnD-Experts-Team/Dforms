<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntryController;

Route::get('/', [EntryController::class, 'index']);
Route::get('/{id}', [EntryController::class, 'show']);
Route::get('/public/{publicIdentifier}', [EntryController::class, 'showByPublicIdentifier']);
Route::post('/toggle-considered', [EntryController::class, 'toggleConsidered']);