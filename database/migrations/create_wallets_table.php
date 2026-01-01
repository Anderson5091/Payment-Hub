Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->enum('operator', ['moncash','natcash']);
    $table->string('dest_wallet_number');
    $table->string('dest_wallet_name');
    $table->boolean('is_default')->default(false);
    $table->boolean('active')->default(true);
    $table->timestamps();
});
