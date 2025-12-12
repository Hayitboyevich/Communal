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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('regulation_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('fish')->nullable();
            $table->foreignId('inspector_id')->index()->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('letter_id')->nullable();
            $table->text('letter_hash_code')->nullable();
            $table->text('letter_message')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
