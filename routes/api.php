<?php

use App\Http\Controllers\Api\PaymentInitController;

Route::post('/payment/init', [PaymentInitController::class, 'init'])
    ->middleware('api.signature');

Route::post('/ping', function (Request $request) {

    $signature = $request->signature;
    $timestamp = $request->timestamp;

    $expected = hash_hmac(
        'sha256',
        json_encode(['timestamp' => $timestamp]),
        config('paymenthub.secret')
    );

    abort_unless(hash_equals($expected, $signature), 403);

    return response()->json(['status' => 'ok']);
});
