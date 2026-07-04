<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('receipt_registers')) {
            return;
        }

        Schema::create('receipt_registers', function (Blueprint $table): void {
            $table->id();
            $table->string('user_code')->nullable()->index();
            $table->string('voucher_no')->nullable()->index();
            $table->string('vtype')->nullable();
            $table->string('invoice')->nullable();
            $table->string('account')->nullable()->index();
            $table->date('tran_date')->nullable();
            $table->date('rec_date')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('add_total', 15, 2)->nullable();
            $table->string('vno_made')->nullable();
            $table->decimal('less_total', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->string('mobile')->nullable();
            $table->text('remark')->nullable();
            $table->text('remark2')->nullable();
            $table->text('remark3')->nullable();
            $table->text('remark4')->nullable();
            $table->string('db_acno')->nullable();
            $table->string('cr_acno')->nullable();
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('cheque_bank')->nullable();
            $table->string('effect', 1)->nullable();
            $table->string('delete_it', 1)->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('oppw')->nullable();
            $table->string('chq_no')->nullable();
            $table->date('chq_date')->nullable();
            $table->string('chq_bank')->nullable();
            $table->string('cancelled', 1)->nullable();
            $table->string('main_acno')->nullable();
            $table->string('single_ent', 1)->nullable();
            $table->string('extra')->nullable();
            $table->string('grno')->nullable();
            $table->date('grdate')->nullable();
            $table->string('add1')->nullable();
            $table->string('add2')->nullable();
            $table->string('add3')->nullable();
            $table->string('add4')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('transport')->nullable();
            $table->string('interstate')->nullable();
            $table->string('crbill')->nullable();
            $table->decimal('taxable', 15, 2)->nullable();
            $table->decimal('cgst_amt', 15, 2)->nullable();
            $table->decimal('sgst_amt', 15, 2)->nullable();
            $table->decimal('igst_amt', 15, 2)->nullable();
            $table->string('state')->nullable()->index();
            $table->string('gst_no')->nullable();
            $table->integer('total_customers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_registers');
    }
};
