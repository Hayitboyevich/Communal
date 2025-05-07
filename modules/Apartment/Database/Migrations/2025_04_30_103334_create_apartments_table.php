<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->index();
            $table->string('street_name')->nullable();
            $table->integer('street_id')->nullable();
            $table->integer('home_id')->nullable();
            $table->string('home_name')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
