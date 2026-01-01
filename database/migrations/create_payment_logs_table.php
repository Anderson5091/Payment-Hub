<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();

            $table->string('event'); 
            // examples:
            // payment_created
            // payment_validated
            // payment_rejected
            // callback_sent
            // callback_failed

            $table->text('message')->nullable();
            $table->json('payload')->nullable();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index('payment_id');
            $table->index('order_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_logs');
    }
};
