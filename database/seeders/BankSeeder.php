<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    public function run()
    {
        Bank::create([
            'name' => 'BNC',
            'currency' => 'HTG',
            'logo' => 'bnc.png',
            'active' => true
        ]);

        Bank::create([
            'name' => 'Sogebank',
            'currency' => 'HTG',
            'logo' => 'sogebank.png',
            'active' => true
        ]);

        Bank::create([
            'name' => 'Unibank',
            'currency' => 'HTG',
            'logo' => 'unibank.png',
            'active' => true
        ]);
    }
}
