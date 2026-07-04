<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ledgers')) {
            return;
        }

        if (! Schema::hasColumn('ledgers', 'import_key')) {
            Schema::table('ledgers', function (Blueprint $table): void {
                $table->string('import_key', 64)->nullable()->after('id');
            });
        }

        DB::statement("
            UPDATE ledgers
            SET import_key = SHA2(CONCAT_WS('|',
                COALESCE(user_code, ''),
                COALESCE(voucher_no, ''),
                COALESCE(vtype, ''),
                COALESCE(acno, ''),
                COALESCE(DATE_FORMAT(tran_date, '%Y-%m-%d'), ''),
                COALESCE(FORMAT(amount, 2), '')
            ), 256)
            WHERE import_key IS NULL OR import_key = ''
        ");

        DB::statement("
            DELETE ledgers
            FROM ledgers
            INNER JOIN (
                SELECT MIN(id) AS keep_id, import_key
                FROM ledgers
                WHERE import_key IS NOT NULL
                GROUP BY import_key
                HAVING COUNT(*) > 1
            ) duplicates
                ON ledgers.import_key = duplicates.import_key
                AND ledgers.id <> duplicates.keep_id
        ");

        Schema::table('ledgers', function (Blueprint $table): void {
            $table->unique('import_key');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ledgers') || ! Schema::hasColumn('ledgers', 'import_key')) {
            return;
        }

        Schema::table('ledgers', function (Blueprint $table): void {
            $table->dropUnique(['import_key']);
            $table->dropColumn('import_key');
        });
    }
};
