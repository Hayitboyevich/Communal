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
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->constrained()->nullOnDelete();
            $table->integer('place_id')->index()->nullable();
            $table->integer('violation_type_id')->index()->nullable();
            $table->text('comment')->index()->nullable();
            $table->text('organization_name')->nullable();
            $table->string('inn')->nullable();
            $table->integer('company_id')->index()->nullable();
            $table->tinyInteger('user_type')->nullable();
            $table->string('pin')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('fish')->nullable();
            $table->string('phone')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
