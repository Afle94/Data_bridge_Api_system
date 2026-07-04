<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('receipt_registers')) {
            return;
        }

        Schema::table('receipt_registers', function (Blueprint $table): void {
            $this->decimalColumn($table, 'add_total');
            $this->stringColumn($table, 'vno_made');
            $this->decimalColumn($table, 'less_total');
            $this->textColumn($table, 'remark2');
            $this->textColumn($table, 'remark3');
            $this->textColumn($table, 'remark4');
            $this->stringColumn($table, 'db_acno');
            $this->stringColumn($table, 'cr_acno');
            $this->stringColumn($table, 'cheque_no');
            $this->dateColumn($table, 'cheque_date');
            $this->stringColumn($table, 'cheque_bank');
            $this->stringColumn($table, 'effect', 1);
            $this->stringColumn($table, 'delete_it', 1);
            $this->decimalColumn($table, 'balance');
            $this->stringColumn($table, 'oppw');
            $this->stringColumn($table, 'chq_no');
            $this->dateColumn($table, 'chq_date');
            $this->stringColumn($table, 'chq_bank');
            $this->stringColumn($table, 'cancelled', 1);
            $this->stringColumn($table, 'main_acno');
            $this->stringColumn($table, 'single_ent', 1);
            $this->stringColumn($table, 'extra');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('receipt_registers')) {
            return;
        }

        Schema::table('receipt_registers', function (Blueprint $table): void {
            foreach ([
                'vno_made',
                'remark2',
                'remark3',
                'remark4',
                'db_acno',
                'cr_acno',
                'cheque_no',
                'cheque_date',
                'cheque_bank',
                'effect',
                'delete_it',
                'balance',
                'oppw',
                'chq_no',
                'chq_date',
                'chq_bank',
                'cancelled',
                'main_acno',
                'single_ent',
                'extra',
            ] as $column) {
                if (Schema::hasColumn('receipt_registers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function stringColumn(Blueprint $table, string $name, int $length = 255): void
    {
        if (! Schema::hasColumn('receipt_registers', $name)) {
            $table->string($name, $length)->nullable();
        }
    }

    private function textColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('receipt_registers', $name)) {
            $table->text($name)->nullable();
        }
    }

    private function dateColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('receipt_registers', $name)) {
            $table->date($name)->nullable();
        }
    }

    private function decimalColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('receipt_registers', $name)) {
            $table->decimal($name, 15, 2)->nullable();
        }
    }
};
