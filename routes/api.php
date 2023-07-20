<?php

use App\Http\Controllers\AccessTokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['validateAppCredentials'])->prefix('token')->controller(AccessTokenController::class)->group(function () {
    Route::get('all', 'index')->name('token.index');
    Route::post('create', 'store')->name('token.store');
    Route::post('revoke', 'revokeAccessToken')->name('token.revoke');
    Route::post('extend', 'extendAccessToken')->name('token.extend');
});
