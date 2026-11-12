<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Push API upsert (ON CONFLICT (home_id)) uchun home_id unique bo'lishi shart.
 *
 * Migratsiyadan oldin dublikatlarni tekshiring:
 *   SELECT home_id, COUNT(*) FROM apartments
 *   WHERE home_id IS NOT NULL GROUP BY home_id HAVING COUNT(*) > 1;
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('apartments')
            ->select('home_id')
            ->whereNotNull('home_id')
            ->groupBy('home_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->pluck('home_id');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'apartments.home_id da dublikatlar bor (masalan: ' . $duplicates->implode(', ') . '). Avval ularni tozalang.'
            );
        }

        Schema::table('apartments', function (Blueprint $table) {
            $table->unique('home_id', 'apartments_home_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropUnique('apartments_home_id_unique');
        });
    }
};
