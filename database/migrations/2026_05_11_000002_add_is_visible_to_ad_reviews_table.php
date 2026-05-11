<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_reviews', function (Blueprint $table) {
            // Default true (published). Admin can hide/show reviews.
            $table->boolean('is_visible')->default(true)->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('ad_reviews', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
