<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_registers')) {
            return;
        }

        Schema::create('purchase_registers', function (Blueprint $table): void {
            $table->id();
            $table->string('user_code')->nullable()->index();
            $table->string('voucher_no')->nullable()->index();
            $table->string('vtype')->nullable();
            $table->string('invoice')->nullable();
            $table->string('account')->nullable()->index();
            $table->date('tran_date')->nullable();
            $table->date('rec_date')->nullable();
            $table->string('manual_no')->nullable();
            $table->string('roadp_no')->nullable();
            $table->string('repl_goods', 1)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->string('mobile')->nullable();
            $table->text('remark')->nullable();
            $table->text('remark2')->nullable();
            $table->text('remark3')->nullable();
            $table->text('remark4')->nullable();
            $table->text('remark5')->nullable();
            $table->text('remark6')->nullable();
            $table->string('grno')->nullable();
            $table->date('grdate')->nullable();
            $table->string('order_no')->nullable();
            $table->date('order_date')->nullable();
            $table->decimal('disc_per', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('dr_side', 15, 2)->nullable();
            $table->decimal('cr_side', 15, 2)->nullable();
            $table->string('add1')->nullable();
            $table->string('add2')->nullable();
            $table->string('add3')->nullable();
            $table->string('add4')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('phone_no')->nullable();
            $table->string('section')->nullable();
            $table->string('transport')->nullable();
            $table->string('interstate')->nullable();
            $table->decimal('add_total', 15, 2)->nullable();
            $table->decimal('less_total', 15, 2)->nullable();
            $table->string('cancels', 1)->nullable();
            $table->string('cc_no')->nullable();
            $table->string('delvat1')->nullable();
            $table->string('delvat2')->nullable();
            $table->string('delvat3')->nullable();
            $table->string('delvat4')->nullable();
            $table->decimal('weight', 15, 3)->nullable();
            $table->integer('boxes')->nullable();
            $table->string('net_billing', 1)->nullable();
            $table->decimal('add_after', 15, 2)->nullable();
            $table->decimal('less_after', 15, 2)->nullable();
            $table->string('crbill')->nullable();
            $table->decimal('taxable', 15, 2)->nullable();
            $table->decimal('sgst_per', 15, 2)->nullable();
            $table->decimal('cgst_per', 15, 2)->nullable();
            $table->decimal('igst_per', 15, 2)->nullable();
            $table->decimal('cgst_amt', 15, 2)->nullable();
            $table->decimal('sgst_amt', 15, 2)->nullable();
            $table->decimal('igst_amt', 15, 2)->nullable();
            $table->string('cancelled', 1)->nullable();
            $table->string('extra')->nullable();
            $table->string('state')->nullable()->index();
            $table->string('gst_no')->nullable();
            $table->integer('total_customers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_registers');
    }
};
