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
        Schema::table('letters', function (Blueprint $table) {
            $table->uuid('internal_uuid')->nullable();
            $table->uuid('shaffof_id_uuid')->nullable();
            $table->text('body_hash')->nullable();
            $table->string('sign_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn('internal_uuid');
            $table->dropColumn('shaffof_id_uuid');
            $table->dropColumn('body_hash');
            $table->dropColumn('sign_url');
        });
    }
};
