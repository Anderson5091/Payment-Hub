<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id');
            $table->string('shop_domain');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['moncash','natcash','bank_online','bank_physical']);

            // SOURCE (client)
            $table->string('src_wallet_number')->nullable();
            $table->string('src_wallet_name')->nullable();

            // DESTINATION (merchant)
            $table->string('dest_wallet_number')->nullable();
            $table->string('dest_wallet_name')->nullable();

            $table->string('transaction_number')->nullable();
            $table->string('proof_path');
            $table->enum('status', ['pending','validated','rejected'])->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
