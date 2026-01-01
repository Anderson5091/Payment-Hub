<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\HmacService;
use Illuminate\Support\Facades\Cache;

class PaymentInitController extends Controller
{
    public function init(Request $request)
    {
        $request->validate([
            'order_id'     => 'required|integer',
            'amount'       => 'required|numeric|min:1',
            'currency'     => 'required|string',
            'shop_domain'  => 'required|string',
            'callback_url' => 'required|url',
        ]);

        $token = Str::uuid()->toString();
        $expiresAt = now()->addMinutes(30);

        Cache::put("payment_token_$token", [
            'order_id' => $request->order_id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'shop_domain' => $request->shop_domain,
            'callback_url' => $request->callback_url,
            'expires_at' => $expiresAt
        ], $expiresAt);

        return response()->json([
            'token' => $token,
            'redirect_url' => url("/pay?token=$token")
        ]);
    }
}
