<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Services\WooCallbackService;

class RetryFailedCallbacks extends Command
{
    protected $signature = 'payment:retry-callbacks';
    protected $description = 'Retry failed WooCommerce callbacks';

    public function handle()
    {
        $payments = Payment::where('status', 'validated')->get();

        foreach ($payments as $payment) {

            $attempts = PaymentLog::where('payment_id', $payment->id)
                ->whereIn('event', [
                    'callback_failed',
                    'callback_error',
                    'callback_retry_failed'
                ])->count();

            if ($attempts >= 3) {
                continue;
            }

            PaymentLog::create([
                'payment_id' => $payment->id,
                'order_id'   => $payment->order_id,
                'event'      => 'callback_retry_scheduled',
                'message'    => 'Retry automatique lancé'
            ]);

            WooCallbackService::send($payment, true);
        }
    }
}
