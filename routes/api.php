<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\PaymentInitController;
use App\Services\HmacService;

Route::post('/payment/init', [PaymentInitController::class, 'init'])
    ->middleware('api.signature');

Route::post('/ping', function (Request $request) {
    $signature = $request->signature;
    $timestamp = $request->timestamp;

    $data = ['timestamp' => $timestamp];
    
    if (!HmacService::verify($data, (string)$signature)) {
        abort(403, 'Invalid signature');
    }

    return response()->json(['status' => 'ok']);
});
