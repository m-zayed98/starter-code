<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nafath_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('trans_id')->unique();
            $table->string('random_code');
            $table->string('status')->default('pending'); // pending, approved, rejected, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('trans_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nafath_verification_requests');
    }
};
