<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transactions table.
     *
     * Morphable so it can be linked to any payable entity (currently AdPackage).
     * The `transactionable` morph columns point to the item being purchased.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic relation to the purchased item (e.g. AdPackage)
            $table->morphs('transactionable');

            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');   // TransactionStatus enum
            $table->string('reference')->unique()->nullable(); // external payment reference
            $table->json('meta')->nullable();               // gateway payload, notes, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
