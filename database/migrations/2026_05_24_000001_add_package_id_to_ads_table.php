<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add package_id to ads so each ad is linked to the subscription package
     * that was active when the ad was created.
     *
     * Nullable because:
     *  - Existing ads pre-date this column.
     *  - Ads created during a free period have no package.
     */
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->foreignId('package_id')
                ->nullable()
                ->after('user_id')
                ->constrained('ad_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
