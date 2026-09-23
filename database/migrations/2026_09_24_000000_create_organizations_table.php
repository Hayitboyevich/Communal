<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1000);
            $table->string('inn')->index();
            $table->string('address')->nullable();
            $table->string('director')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('region_id')->nullable()->index()->constrained('regions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->index()->constrained('districts')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('position', 1000)->nullable();
            $table->date('begin_date')->nullable();
            $table->timestamp('dismissed_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX organizations_inn_unique ON organizations (inn) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX user_organizations_user_id_organization_id_unique ON user_organizations (user_id, organization_id) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_organizations');
        Schema::dropIfExists('organizations');
    }
};
