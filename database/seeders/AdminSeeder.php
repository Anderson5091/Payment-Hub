<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin Payment Hub',
            'email' => 'admin@payment_hub.com',//admin@paymenthub.com before
            'password' => Hash::make('ChangeMe123!'),
            'is_admin' => true
        ]);
    }
}
