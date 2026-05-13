<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();

            // Nullable: guests can view ads without being authenticated
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // view | call | whatsapp
            $table->string('type');

            $table->timestamps();

            // One action of each type per user per ad (prevents duplicate counting)
            // For guests (user_id = null) we allow multiple rows — they are not deduplicated
            $table->unique(['ad_id', 'user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_actions');
    }
};
