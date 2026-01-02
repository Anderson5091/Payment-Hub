<?php

use App\Http\Controllers\Api\PaymentInitController;

Route::post('/payment/init', [PaymentInitController::class, 'init'])
    ->middleware('api.signature');
