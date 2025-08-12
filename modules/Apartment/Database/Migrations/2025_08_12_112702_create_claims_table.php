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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->index()->constrained('users');
            $table->foreignId('user_id')->index()->nullable()->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->string('cadastral_number')->nullable();
            $table->string('responsible_pin')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->text('address')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
