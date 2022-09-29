<?php

use Illuminate\Support\Facades\Route;
use Outl1ne\NovaTwoFactor\Http\Controller\TwoFactorController;

Route::get('/register', [TwoFactorController::class, 'registerUser']);
Route::match(['get', 'post'], '/recover', [TwoFactorController::class, 'recover'])->name('nova-two-factor.recover');
Route::get('/status', [TwoFactorController::class, 'getStatus']);
Route::post('/confirm', [TwoFactorController::class, 'verifyOtp']);
Route::post('/toggle', [TwoFactorController::class, 'toggle2Fa']);
Route::post('/authenticate', [TwoFactorController::class, 'authenticate'])->name('nova-two-factor.auth');
