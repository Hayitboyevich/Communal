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
        Schema::create('program_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->string('quarter_name')->nullable();
            $table->string('street_name')->nullable();
            $table->string('apartment_number')->nullable();
            $table->tinyInteger('status')->index()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_objects');
    }
};
