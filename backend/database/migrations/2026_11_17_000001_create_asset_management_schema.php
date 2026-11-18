<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema inti Project B, mengikuti SQL yang disediakan tim.
     * Tambahan: tabel divisions + users.division_id.
     * personal_access_tokens tetap disediakan otomatis oleh migration Sanctum.
     */
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->enum('role', ['ADMIN', 'USER'])->default('USER');
            $table->unsignedBigInteger('division_id')->nullable();
            $table->foreign('division_id')->references('id')->on('divisions');
        });

        Schema::create('assets_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('assets_code', 40)->unique();
            $table->string('name', 200);
            $table->unsignedBigInteger('category_id');
            $table->enum('status', ['AVAILABLE', 'BORROWED', 'MAINTENANCE'])->default('AVAILABLE');
            $table->foreign('category_id')->references('id')->on('assets_categories');
            $table->index('category_id', 'idx_assets_category');
        });

        Schema::create('assets_loan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assets_id');
            $table->unsignedBigInteger('borrower_id');
            $table->dateTime('borrowed_at')->useCurrent();
            $table->dateTime('returned_at')->nullable();
            $table->enum('status', ['BORROWED', 'RETURNED'])->default('BORROWED');
            $table->foreign('assets_id')->references('id')->on('assets');
            $table->foreign('borrower_id')->references('id')->on('users');
            $table->index('assets_id', 'idx_assets_loan_assets');
            $table->index('borrower_id', 'idx_assets_loan_borrower');
        });

        // AST-08: kolom turunan + unique index. MySQL mengizinkan banyak NULL,
        // sehingga riwayat RETURNED bebas bertambah tetapi hanya satu loan aktif.
        DB::statement("
            ALTER TABLE assets_loan
            ADD COLUMN active_assets_id BIGINT UNSIGNED
            GENERATED ALWAYS AS (IF(returned_at IS NULL, assets_id, NULL)) STORED
        ");
        DB::statement('ALTER TABLE assets_loan ADD UNIQUE KEY uq_assets_loan_active (active_assets_id)');
        DB::statement("
            ALTER TABLE assets_loan ADD CONSTRAINT chk_loan_consistent CHECK (
                (status = 'BORROWED' AND returned_at IS NULL) OR
                (status = 'RETURNED' AND returned_at IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('assets_loan');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('assets_categories');
        Schema::dropIfExists('users');
        Schema::dropIfExists('divisions');
    }
};
