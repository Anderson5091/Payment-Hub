Schema::create('banks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('currency')->default('HTG');
    $table->string('logo')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});
