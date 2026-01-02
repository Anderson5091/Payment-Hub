<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'shop_domain',
        'amount',
        'method',
        'src_wallet_number',
        'src_wallet_name',
        'dest_wallet_number',
        'dest_wallet_name',
        'transaction_number',
        'proof_path',
        'status'
    ];
}
