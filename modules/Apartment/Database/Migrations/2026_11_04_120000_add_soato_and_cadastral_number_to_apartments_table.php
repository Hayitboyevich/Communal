<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->string('region_soato', 20)->nullable()->index();
            $table->string('district_soato', 20)->nullable()->index();
            $table->string('cadastral_number', 64)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['region_soato']);
            $table->dropIndex(['district_soato']);
            $table->dropIndex(['cadastral_number']);
            $table->dropColumn(['region_soato', 'district_soato', 'cadastral_number']);
        });
    }
};
