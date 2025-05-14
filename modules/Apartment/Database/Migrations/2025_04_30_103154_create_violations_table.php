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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained('regulations')->onDelete('cascade');
            $table->text('desc')->nullable();
            $table->date('deadline')->nullable();
            $table->tinyInteger('type');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
