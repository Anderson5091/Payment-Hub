<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

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
            abort(403);
        }

        $payment->update(['status' => 'validated']);

        return redirect('/admin/payments')->with('success', 'Paiement validé');
    }

    public function rejectPayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            abort(403);
        }

        $payment->update(['status' => 'rejected']);

        return redirect('/admin/payments')->with('success', 'Paiement rejeté');
    }
}
