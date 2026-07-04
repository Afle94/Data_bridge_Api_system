<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledgers')) {
            return;
        }

        Schema::create('ledgers', function (Blueprint $table): void {
            $table->id();
            $table->string('import_key', 64)->unique();
            $table->string('user_code')->nullable()->index();
            $table->string('voucher_no')->nullable()->index();
            $table->string('vtype')->nullable()->index();
            $table->string('dtype')->nullable();
            $table->string('tran_type', 1)->nullable();
            $table->string('acno')->nullable()->index();
            $table->string('achead')->nullable()->index();
            $table->date('tran_date')->nullable()->index();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('sales_agent')->nullable();
            $table->text('remark1')->nullable();
            $table->text('remark2')->nullable();
            $table->text('remark3')->nullable();
            $table->text('remark4')->nullable();
            $table->text('remark5')->nullable();
            $table->string('adjustment', 1)->nullable();
            $table->string('add_flag', 1)->nullable();
            $table->string('less_flag', 1)->nullable();
            $table->string('opening', 1)->nullable();
            $table->string('crbill', 1)->nullable();
            $table->decimal('disc_per', 15, 2)->nullable();
            $table->decimal('on_amount', 15, 2)->nullable();
            $table->decimal('percent', 15, 2)->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->string('calc', 1)->nullable();
            $table->text('ms')->nullable();
            $table->string('add_less', 1)->nullable();
            $table->decimal('adj_per', 15, 2)->nullable();
            $table->string('adj_type')->nullable();
            $table->string('vat_adj', 1)->nullable();
            $table->string('cancelled', 1)->nullable();
            $table->string('vno_made')->nullable();
            $table->string('single_ent', 1)->nullable();
            $table->string('salesman')->nullable();
            $table->string('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
