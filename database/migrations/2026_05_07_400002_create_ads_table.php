<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();

            // ── Ownership ──────────────────────────────────────────────────
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ── FAL / NHC identifiers ──────────────────────────────────────
            // fal_license_number is denormalized here for quick lookup / uniqueness guard
            $table->string('fal_license_number')->nullable();

            // Per-ad advertisement license number (unique across the app)
            $table->string('ad_license_number')->unique();

            // ── NHC data (all fields returned by NHC stored as JSON) ───────
            $table->json('nhc_data')->nullable();

            // ── Ad lifecycle ──────────────────────────────────────────────
            // draft → published (after all steps completed)
            $table->string('status')->default('draft');

            // sale | rent
            $table->string('purpose')->nullable();

            // ── Step 2: user-editable fields ──────────────────────────────
            $table->string('title', 50)->nullable();
            $table->text('description')->nullable();

            // new | used | under_construction
            $table->string('apartment_condition')->nullable();

            // Required when purpose = sale
            $table->string('deed_number')->nullable();

            // ── Step 3: apartment details ─────────────────────────────────
            $table->unsignedSmallInteger('living_rooms_count')->nullable();
            $table->unsignedSmallInteger('bathrooms_count')->nullable();
            $table->unsignedSmallInteger('floor')->nullable();

            // furnished | unfurnished
            $table->string('furnishing_status')->nullable();

            // ── Pricing ───────────────────────────────────────────────────
            $table->decimal('price', 12, 2)->nullable();

            // daily | weekly | monthly | yearly (only when purpose = rent)
            $table->string('rental_period')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
