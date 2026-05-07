<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add subscription-specific columns:
     *  - ad_count        : original quota from the package at subscription time
     *  - user_ads_count  : how many ads the user has posted under this subscription
     *  - package_price   : snapshot of the package price at subscription time
     *  - status          : active | expired | cancelled (replaces is_cancelled boolean)
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('ad_count')->after('ad_package_id');
            $table->integer('user_ads_count')->default(0)->after('ad_count');
            $table->decimal('package_price', 10, 2)->after('user_ads_count');
            $table->string('status')->default('active')->after('package_price');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['ad_count', 'user_ads_count', 'package_price', 'status']);
        });
    }
};
