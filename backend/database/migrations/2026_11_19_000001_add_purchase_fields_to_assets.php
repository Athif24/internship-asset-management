<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah dua kolom baru pada tabel assets.
     * Kolom lama tidak diubah, rename, atau dihapus.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('purchase_price', 14, 2)->nullable()->after('name');
            $table->unsignedSmallInteger('purchase_year')->nullable()->after('purchase_price');
        });

        DB::statement(
            'ALTER TABLE assets ADD CONSTRAINT chk_assets_purchase_year '
            . 'CHECK (purchase_year IS NULL OR purchase_year BETWEEN 1900 AND ' . (date('Y') + 1) . ')'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE assets DROP CHECK chk_assets_purchase_year');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'purchase_year']);
        });
    }
};
