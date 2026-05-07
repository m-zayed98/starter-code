<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Required reason text (10–500 chars)
            $table->text('reason');

            $table->timestamps();

            // One report per user per ad
            $table->unique(['ad_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_reports');
    }
};
