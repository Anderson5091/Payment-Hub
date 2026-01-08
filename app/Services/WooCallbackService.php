<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Http;

class WooCallbackService
{
    public static function send(Payment $payment, bool $isRetry = false): bool
    {
        $payload = [
            'order_id'  => $payment->order_id,
            'status'    => $payment->status,
            'amount'    => $payment->amount,
            'method'    => $payment->method,
            'reference' => $payment->reference,
            'timestamp' => time(),
        ];

        $secret = config('app.payment_hub_secret');
        $payload['signature'] = HmacService::sign($payload, (string)$secret);

        try {
            $response = Http::timeout(10)->post(
                $payment->callback_url,
                $payload
            );

            if ($response->successful()) {

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'order_id'   => $payment->order_id,
                    'event'      => $isRetry ? 'callback_retry_success' : 'callback_sent',
                    'message'    => $response->body(),
                    'payload'    => $payload,
                ]);

                return true;
            }

            PaymentLog::create([
                'payment_id' => $payment->id,
                'order_id'   => $payment->order_id,
                'event'      => $isRetry ? 'callback_retry_failed' : 'callback_failed',
                'message'    => $response->body(),
                'payload'    => $payload,
            ]);

        } catch (\Exception $e) {

            PaymentLog::create([
                'payment_id' => $payment->id,
                'order_id'   => $payment->order_id,
                'event'      => 'callback_error',
                'message'    => $e->getMessage(),
                'payload'    => $payload,
            ]);
        }

        return false;
    }
}
