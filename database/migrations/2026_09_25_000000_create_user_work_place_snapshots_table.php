<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_work_place_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->jsonb('response');
            $table->string('hash', 32);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
        });

        // current_work_place avvalgi versiyadagi migration'dan qolgan bo'lishi mumkin
        if (Schema::hasColumn('users', 'current_work_place')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('current_work_place');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_work_place_snapshots');
    }
};
