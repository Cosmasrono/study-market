<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

// Disable CSRF and CORS protection for M-Pesa callback
Route::post('/mpesa/callback', [MpesaController::class, 'callback'])
    ->name('mpesa.callback')
    ->withoutMiddleware(['web', 'csrf', 'cors']);
