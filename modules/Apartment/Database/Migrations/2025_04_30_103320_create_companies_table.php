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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->integer('region_id')->nullable()->index();
            $table->integer('district_id')->nullable()->index();
            $table->integer('company_id')->nullable();
            $table->string('country_soato')->nullable();
            $table->string('region_soato')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_adress')->nullable();
            $table->string('company_director')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_tin')->nullable();
            $table->string('company_account')->nullable();
            $table->string('company_mfo')->nullable();
            $table->string('company_bank')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
