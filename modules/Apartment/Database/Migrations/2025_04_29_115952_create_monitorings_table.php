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
        Schema::create('monitorings', function (Blueprint $table) {
            $table->id();
            $table->integer('monitoring_status_id')->nullable()->index();
            $table->tinyInteger('step')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->integer('user_id')->nullable()->index();
            $table->integer('role_id')->nullable()->index();
            $table->integer('monitoring_type_id')->index()->nullable()->comment('organish turi');
            $table->integer('monitoring_base_id')->index()->nullable()->comment('organish uchun asos');
            $table->integer('company_id')->index()->nullable()->comment('boshqaruv organi');
            $table->integer('apartment_id')->index()->nullable()->comment('boshqaruvdagi uy');
            $table->integer('region_id')->index()->nullable();
            $table->integer('district_id')->index()->nullable();
            $table->text('address_commit')->index()->nullable()->comment('organish otkazilgan joy haqida malumot');
            $table->string('lat')->nullable()->comment('lat');
            $table->string('long')->nullable()->comment('long');
            $table->boolean('is_administrative')->default(false);
            $table->boolean('send_court')->default(false);
            $table->boolean('send_mib')->default(false);
            $table->boolean('send_chora')->default(false);
            $table->text('additional_comment')->nullable()->comment('Qoidabuzarlik aniqlanmadi comment');
            $table->jsonb('additional_files')->nullable()->comment('Qoidabuzarlik aniqlanmadi file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorings');
    }
};
