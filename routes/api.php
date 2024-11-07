<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user()->tokens;
})->middleware('auth:sanctum');


Route::group([
    'prefix' => 'auth',
    'controller' => \App\Http\Controllers\Auth\AuthController::class,
], function () {
    Route::post('register', 'register');
    Route::post('verify-account', 'verifyAccount');
    Route::post('login', 'login');
});
