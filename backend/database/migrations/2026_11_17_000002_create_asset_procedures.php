<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Aturan bisnis AST-02 s.d. AST-09 dijalankan di level database.
     * Kata kunci DELIMITER sengaja tidak dipakai: itu perintah klien MySQL,
     * bukan SQL yang bisa dikirim melalui PDO.
     */
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS borrow_asset');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE borrow_asset(
    IN  p_asset_id    BIGINT UNSIGNED,
    IN  p_borrower_id BIGINT UNSIGNED,
    OUT p_loan_id     BIGINT UNSIGNED
)
BEGIN
    DECLARE v_status VARCHAR(20) DEFAULT NULL;
    DECLARE v_user_count INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SET SESSION innodb_lock_wait_timeout = 3;

    START TRANSACTION;

    SELECT status INTO v_status
    FROM assets
    WHERE id = p_asset_id
    FOR UPDATE;

    IF v_status IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ASSET_NOT_FOUND: aset tidak ditemukan';
    END IF;

    SELECT COUNT(*) INTO v_user_count FROM users WHERE id = p_borrower_id;
    IF v_user_count = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'USER_NOT_FOUND: peminjam tidak ditemukan';
    END IF;

    IF v_status = 'BORROWED' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AST-03: aset sedang dipinjam';
    ELSEIF v_status = 'MAINTENANCE' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AST-04: aset sedang dalam maintenance';
    END IF;

    INSERT INTO assets_loan (assets_id, borrower_id, borrowed_at, status)
    VALUES (p_asset_id, p_borrower_id, NOW(), 'BORROWED');

    SET p_loan_id = LAST_INSERT_ID();

    UPDATE assets SET status = 'BORROWED' WHERE id = p_asset_id;

    COMMIT;
END
SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS return_asset');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE return_asset(
    IN  p_asset_id BIGINT UNSIGNED,
    IN  p_user_id  BIGINT UNSIGNED,
    OUT p_loan_id  BIGINT UNSIGNED
)
BEGIN
    DECLARE v_asset_status VARCHAR(20) DEFAULT NULL;
    DECLARE v_loan_id      BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_borrower_id  BIGINT UNSIGNED DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SET SESSION innodb_lock_wait_timeout = 3;

    START TRANSACTION;

    SELECT status INTO v_asset_status
    FROM assets
    WHERE id = p_asset_id
    FOR UPDATE;

    IF v_asset_status IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ASSET_NOT_FOUND: aset tidak ditemukan';
    END IF;

    SELECT id, borrower_id INTO v_loan_id, v_borrower_id
    FROM assets_loan
    WHERE assets_id = p_asset_id AND returned_at IS NULL
    FOR UPDATE;

    IF v_loan_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AST-06: aset tidak sedang dipinjam';
    END IF;

    IF v_borrower_id <> p_user_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AST-07: pengembalian hanya boleh oleh peminjam';
    END IF;

    UPDATE assets_loan
       SET returned_at = NOW(), status = 'RETURNED'
     WHERE id = v_loan_id;

    UPDATE assets SET status = 'AVAILABLE' WHERE id = p_asset_id;

    SET p_loan_id = v_loan_id;

    COMMIT;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS borrow_asset');
        DB::unprepared('DROP PROCEDURE IF EXISTS return_asset');
    }
};
