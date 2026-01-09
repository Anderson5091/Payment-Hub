<?php

namespace App\Http\Controllers\Admin;
//rename folder
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Services\WooCallbackService;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments', [
            'payments' => Payment::where('status', 'pending')->latest()->get()
        ]);
    }

    public function show($id)
    {
        return view('admin.payment_show', [
            'payment' => Payment::findOrFail($id)
        ]);
    }

    public function validatePayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            abort(403, 'Paiement déjà traité');
        }

        $payment->status = 'validated';
        $payment->save();

        // LOG
        PaymentLog::create([
            'payment_id' => $payment->id,
            'order_id'   => $payment->order_id,
            'event'      => 'payment_validated',
            'message'    => 'Paiement validé par admin',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // CALLBACK WOOCOMMERCE
        WooCallbackService::send($payment);

        return redirect('/admin/payments')
            ->with('success', 'Paiement validé');
    }

    public function rejectPayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            abort(403, 'Paiement déjà traité');
        }

        $payment->status = 'rejected';
        $payment->save();

        // LOG
        PaymentLog::create([
            'payment_id' => $payment->id,
            'order_id'   => $payment->order_id,
            'event'      => 'payment_rejected',
            'message'    => 'Paiement rejeté par admin',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // CALLBACK OPTIONNEL
        WooCallbackService::send($payment);

        return redirect('/admin/payments')
            ->with('success', 'Paiement rejeté');
    }
}
