<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Apartment\Enums\ApartmentsTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->smallInteger('apartment_type')->nullable()->index()->after('id');
        });
        $values = implode(', ', array_column(ApartmentsTypeEnum::cases(), 'value'));
        DB::statement("ALTER TABLE apartments ADD CONSTRAINT apartments_type_check CHECK (apartment_type IN ({$values}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('apartment_type');
        });
    }
};
