<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apartment_hidden_economies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('apartment_id');
            $table->bigInteger('home_id')->index();
            $table->integer('hidden_economy_type');
            $table->unsignedBigInteger('monitoring_type_id')->index();
            $table->foreign('monitoring_type_id')->references('id')->on('monitoring_types');
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_hidden_economies');
    }
};
