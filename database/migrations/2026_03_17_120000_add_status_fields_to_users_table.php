<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('identity_number');
            $table->text('disabled_reason')->nullable()->after('status');
            $table->timestamp('disabled_at')->nullable()->after('disabled_reason');
        });
    }

    public function down(): void {}
};
