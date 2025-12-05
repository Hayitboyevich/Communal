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
        Schema::create('program_object_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checklist_id')->index()->nullable()->constrained()->nullOnDelete();
            $table->string('plan');
            $table->string('unit');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_object_checklists');
    }
};
