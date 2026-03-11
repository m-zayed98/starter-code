<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();

            $table->morphs('otpable');

            $table->string('code', 32);
            $table->string('purpose', 100);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            $table->index(['otpable_type', 'otpable_id', 'purpose', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};

