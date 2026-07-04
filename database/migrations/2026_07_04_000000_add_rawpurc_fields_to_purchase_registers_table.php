<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_registers')) {
            return;
        }

        Schema::table('purchase_registers', function (Blueprint $table): void {
            $this->stringColumn($table, 'manual_no');
            $this->stringColumn($table, 'roadp_no');
            $this->stringColumn($table, 'repl_goods', 1);
            $this->textColumn($table, 'remark2');
            $this->textColumn($table, 'remark3');
            $this->textColumn($table, 'remark4');
            $this->textColumn($table, 'remark5');
            $this->textColumn($table, 'remark6');
            $this->stringColumn($table, 'order_no');
            $this->dateColumn($table, 'order_date');
            $this->decimalColumn($table, 'disc_per');
            $this->decimalColumn($table, 'discount');
            $this->decimalColumn($table, 'dr_side');
            $this->decimalColumn($table, 'cr_side');
            $this->stringColumn($table, 'phone_no');
            $this->stringColumn($table, 'section');
            $this->stringColumn($table, 'cancels', 1);
            $this->stringColumn($table, 'cc_no');
            $this->stringColumn($table, 'delvat1');
            $this->stringColumn($table, 'delvat2');
            $this->stringColumn($table, 'delvat3');
            $this->stringColumn($table, 'delvat4');
            $this->decimalColumn($table, 'weight', 15, 3);
            $this->integerColumn($table, 'boxes');
            $this->stringColumn($table, 'net_billing', 1);
            $this->decimalColumn($table, 'add_after');
            $this->decimalColumn($table, 'less_after');
            $this->decimalColumn($table, 'sgst_per');
            $this->decimalColumn($table, 'cgst_per');
            $this->decimalColumn($table, 'igst_per');
            $this->stringColumn($table, 'cancelled', 1);
            $this->stringColumn($table, 'extra');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_registers')) {
            return;
        }

        Schema::table('purchase_registers', function (Blueprint $table): void {
            foreach ([
                'manual_no',
                'roadp_no',
                'repl_goods',
                'remark2',
                'remark3',
                'remark4',
                'remark5',
                'remark6',
                'order_no',
                'order_date',
                'disc_per',
                'discount',
                'dr_side',
                'cr_side',
                'phone_no',
                'section',
                'cancels',
                'cc_no',
                'delvat1',
                'delvat2',
                'delvat3',
                'delvat4',
                'weight',
                'boxes',
                'net_billing',
                'add_after',
                'less_after',
                'sgst_per',
                'cgst_per',
                'igst_per',
                'cancelled',
                'extra',
            ] as $column) {
                if (Schema::hasColumn('purchase_registers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function stringColumn(Blueprint $table, string $name, int $length = 255): void
    {
        if (! Schema::hasColumn('purchase_registers', $name)) {
            $table->string($name, $length)->nullable();
        }
    }

    private function textColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('purchase_registers', $name)) {
            $table->text($name)->nullable();
        }
    }

    private function dateColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('purchase_registers', $name)) {
            $table->date($name)->nullable();
        }
    }

    private function decimalColumn(Blueprint $table, string $name, int $precision = 15, int $scale = 2): void
    {
        if (! Schema::hasColumn('purchase_registers', $name)) {
            $table->decimal($name, $precision, $scale)->nullable();
        }
    }

    private function integerColumn(Blueprint $table, string $name): void
    {
        if (! Schema::hasColumn('purchase_registers', $name)) {
            $table->integer($name)->nullable();
        }
    }
};
