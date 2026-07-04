<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_registers', 'user_code')) {
            Schema::table('sale_registers', function (Blueprint $table): void {
                $table->string('user_code')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_registers', 'user_code')) {
            Schema::table('sale_registers', function (Blueprint $table): void {
                $table->dropIndex(['user_code']);
                $table->dropColumn('user_code');
            });
        }
    }
};
