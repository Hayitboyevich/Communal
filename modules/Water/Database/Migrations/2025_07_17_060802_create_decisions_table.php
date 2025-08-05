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
            $table->string('project_id')->index()->comment('1-suv, 2-kop kavartira');
            $table->foreignId('guid')->constrained()->cascadeOnDelete();
            $table->bigInteger('parent_id')->nullable()->index();
            $table->timestamp('created_time')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->bigInteger('region_id')->nullable();
            $table->bigInteger('district_id')->nullable();
            $table->string('protocol_article_part')->nullable();
            $table->string('inspector_pinpp')->nullable();
            $table->string('series')->nullable()->index();
            $table->string('number')->nullable()->index();
            $table->string('decision_series')->nullable()->index();
            $table->string('decision_number')->nullable()->index();
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
            $table->string('execution_date')->nullable();
            $table->string('main_punishment_type')->nullable();
            $table->string('main_punishment_amount')->nullable();
            $table->string('resolution_organ')->nullable();
            $table->string('adm_case_organ')->nullable();
            $table->string('resolution_consider_info')->nullable();
            $table->string('paid_amount')->nullable();
            $table->integer('decision_status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
