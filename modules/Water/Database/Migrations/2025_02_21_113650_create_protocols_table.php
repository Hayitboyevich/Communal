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
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('step')->nullable();
            $table->tinyInteger('category')->nullable();
            $table->integer('inspector_id')->index()->nullable();
            $table->boolean('is_administrative')->default(false);
            $table->foreignId('protocol_type_id')->nullable()->index()->constrained('protocol_types')->nullOnDelete();
            $table->foreignId('protocol_status_id')->nullable()->index()->constrained('protocol_statuses')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->index()->constrained('regions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->index()->constrained('districts')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('fish')->nullable()->comment('ogoh fuqarodan keladi');
            $table->string('phone_number')->nullable()->comment('Ogoh fuqarodan keladi');
            $table->text('description')->nullable()->comment('1-qadamdagi qoshimcha malumot');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            // 2-qadam
            $table->tinyInteger('user_type')->nullable()->comment('Javobgar turi');
            $table->string('inn')->nullable();
            $table->string('enterprise_name')->nullable()->comment('korxona nomi');
            $table->string('pin')->nullable()->comment('jshshir');
            $table->date('birth_date')->nullable()->comment('tugilgan kuni');
            $table->string('functionary_name')->nullable()->comment('mansabdor shaxs');
            $table->string('phone')->nullable()->comment('tel nomer');
            $table->string('self_government_name')->nullable()->comment('fuqaroni ozini ozi boshqarish organi');
            $table->string('inspector_name')->nullable()->comment('fuqaroni ozini ozi boshqarish organi');
            $table->string('participant_name')->nullable()->comment('boshqa ishtirok etuvchi');
            $table->jsonb('additional_files')->nullable()->comment('Kamchilik aniqlanmadi file');
            $table->text('additional_comment')->nullable()->comment('Kamchilik aniqlanmadi comment');
            //3-qadam
            $table->text('defect_information')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->jsonb('image_files')->nullable();
            // qoshimcha
            $table->integer('role_id')->nullable()->index()->comment('kim tomodidan yaratilganligi role');
            $table->bigInteger('user_id')->nullable()->index()->comment('kim tomodidan yaratilganligi user');
            $table->tinyInteger('type')->nullable()->comment('qaysi platformada yaratilgani 1 suv, 2 ogoh fuqaro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
