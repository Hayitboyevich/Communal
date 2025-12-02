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
        Schema::create('program_regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_monitoring_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_object_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checklist_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_object_checklist_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->string('plan')->nullable();
            $table->string('all')->nullable();
            $table->string('need_repair')->nullable();
            $table->string('done')->nullable();
            $table->string('progress')->nullable();
            $table->string('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_regulations');
    }
};
