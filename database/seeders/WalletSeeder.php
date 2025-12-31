<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    public function run()
    {
        Wallet::create([
            'operator' => 'moncash',
            'dest_wallet_number' => 'HT12345678',
            'dest_wallet_name' => 'Merchant MonCash',
            'is_default' => true
        ]);

        Wallet::create([
            'operator' => 'natcash',
            'dest_wallet_number' => 'HT87654321',
            'dest_wallet_name' => 'Merchant NatCash',
            'is_default' => true
        ]);
    }
}
