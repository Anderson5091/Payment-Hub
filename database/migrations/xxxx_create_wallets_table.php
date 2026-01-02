<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->enum('operator', ['moncash','natcash']);
            $table->string('dest_wallet_number');
            $table->string('dest_wallet_name');
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallets');
    }
};
