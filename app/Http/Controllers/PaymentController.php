<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Wallet;
use App\Models\Bank;

use App\Models\Payment;
use App\Services\FileUploadService;

class PaymentController extends Controller
{
    public function showForm(Request $request)
    {
        $token = $request->query('token');
        $data = Cache::get("payment_token_$token");

        if (!$data || now()->greaterThan($data['expires_at'])) {
            abort(403, 'Invalid or expired token');
        }

        return view('payment.form', [
            'token' => $token,
            'amount' => $data['amount'],
            'wallets' => Wallet::where('active', true)->get(),
            'banks' => Bank::where('active', true)->get()
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'method' => 'required|in:moncash,natcash,bank_online,bank_physical',
            'proof' => 'required|file',
            'src_wallet_number' => 'nullable|string',
            'src_wallet_name' => 'nullable|string',
            'transaction_number' => 'nullable|string'
        ]);

        $data = Cache::get("payment_token_{$request->token}");
        if (!$data || now()->greaterThan($data['expires_at'])) {
            abort(403, 'Invalid or expired token');
        }

        $proofPath = FileUploadService::uploadProof($request->file('proof'));

        $payment = Payment::create([
            'order_id' => $data['order_id'],
            'shop_domain' => $data['shop_domain'],
            'amount' => $data['amount'],
            'method' => $request->method,
            'src_wallet_number' => $request->src_wallet_number,
            'src_wallet_name' => $request->src_wallet_name,
            'transaction_number' => $request->transaction_number,
            'proof_path' => $proofPath,
            'status' => 'pending'
        ]);

        return view('payment.success', [
            'reference' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)
        ]);
    }
}

