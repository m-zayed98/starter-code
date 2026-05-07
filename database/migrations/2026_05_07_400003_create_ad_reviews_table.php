<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 1–5 stars (required)
            $table->unsignedTinyInteger('rating');

            // Optional written feedback (max 500 chars)
            $table->text('feedback')->nullable();

            $table->timestamps();

            // One review per user per ad
            $table->unique(['ad_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_reviews');
    }
};
