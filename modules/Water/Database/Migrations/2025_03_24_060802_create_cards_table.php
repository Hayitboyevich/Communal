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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->index()->constrained('users')->nullOnDelete();
            $table->string('full_number')->nullable();
            $table->string('first6')->nullable();
            $table->string('last4')->nullable();
            $table->string('expMonth')->nullable();
            $table->string('expYear')->nullable();
            $table->string('bin')->nullable();
            $table->string('cardHolder')->nullable();
            $table->string('bankName')->nullable();
            $table->string('bankCode')->nullable();
            $table->string('token')->nullable();
            $table->string('hashPan')->nullable();
            $table->string('processing')->nullable();
            $table->string('type')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
