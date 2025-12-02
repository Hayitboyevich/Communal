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
        Schema::create('program_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_object_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->foreignId('user_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('role_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_monitorings');
    }
};
