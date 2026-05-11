<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_reports', function (Blueprint $table) {
            // Admin reply text sent to user via email
            $table->text('reply')->nullable()->after('reason');

            // replied | pending
            $table->string('status')->default('pending')->after('reply');

            // Timestamp when admin replied
            $table->timestamp('replied_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ad_reports', function (Blueprint $table) {
            $table->dropColumn(['reply', 'status', 'replied_at']);
        });
    }
};
