<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('charge_id')->references('id')->on('charges');
            $table->foreignId('card_id')->references('id')->on('cards');
            $table->string('payment_id');
            $table->integer('wage')->nullable();
            $table->integer('ref_number')->nullable();
            $table->integer('tracking_code')->nullable();
            $table->string('payment_gateway', 10);
            $table->tinyInteger('status')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
