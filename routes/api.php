<?php

use App\Http\Controllers\XummController;
use Illuminate\Support\Facades\Route;

Route::prefix('/xumm')->group( function () {
    Route::post('/payload', [XummController::class, 'createPayload']);
    Route::get('/callback', [XummController::class, 'callback']);
});
