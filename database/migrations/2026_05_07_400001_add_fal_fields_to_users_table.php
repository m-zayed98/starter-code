<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds FAL (Real Estate Authority) advertiser profile fields to the users table.
 *
 * These fields are saved once on first ad creation and auto-populated
 * on subsequent ads. The ad_license_number is per-ad and lives in the ads table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // FAL license number (6–12 digits) – saved to user profile
            $table->string('fal_license_number')->nullable()->after('verified_by_nafath');

            // Mobile number registered with the Real Estate Authority
            $table->string('nhc_mobile')->nullable()->after('fal_license_number');

            // Advertiser type (broker, investor, appraiser, property_manager, developer)
            $table->string('advertiser_type')->nullable()->after('nhc_mobile');

            // Commercial registration – required for developer / investor types
            $table->string('commercial_registration_number')->nullable()->after('advertiser_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fal_license_number',
                'nhc_mobile',
                'advertiser_type',
                'commercial_registration_number',
            ]);
        });
    }
};
