<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('protocol_id')->nullable();
            $table->bigInteger('event_id')->nullable();
            $table->timestamp('created_time')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->bigInteger('region_id')->nullable();
            $table->bigInteger('district_id')->nullable();
            $table->bigInteger('organ_id')->nullable();
            $table->string('protocol_article_part')->nullable();
            $table->string('inspector_pinpp')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('series')->nullable();
            $table->string('number')->nullable();
            $table->string('decision_series')->nullable();
            $table->string('decision_number')->nullable();
            $table->integer('status')->nullable();
            $table->string('status_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('second_name')->nullable();
            $table->string('document_series')->nullable();
            $table->string('document_number')->nullable();
            $table->string('pinpp')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('employment_place')->nullable();
            $table->string('employment_position')->nullable();
            $table->integer('decision_type_id')->nullable();
            $table->string('decision_type_name')->nullable();
            $table->date('execution_date')->nullable();
            $table->string('main_punishment_type')->nullable();
            $table->string('main_punishment_amount')->nullable();
            $table->bigInteger('resolution_organ_id')->nullable();
            $table->bigInteger('adm_case_organ_id')->nullable();
            $table->string('resolution_organ')->nullable();
            $table->string('adm_case_organ')->nullable();
            $table->string('resolution_consider_info')->nullable();
            $table->string('discount_amount_70')->nullable();
            $table->string('discount_amount_50')->nullable();
            $table->string('discount_amount_30')->nullable();
            $table->date('discount_for_date_70')->nullable();
            $table->date('discount_for_date_50')->nullable();
            $table->date('discount_for_date_30')->nullable();
            $table->integer('termination_reason_id')->nullable();
            $table->string('paid_amount')->nullable();
            $table->integer('decision_status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
