<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sale_registers')) {
            return;
        }

        Schema::create('sale_registers', function (Blueprint $table): void {
            $table->id();
            $table->string('voucher_no')->nullable()->index();
            $table->string('vtype')->nullable();
            $table->string('invoice')->nullable();
            $table->string('account')->nullable()->index();
            $table->date('tran_date')->nullable();
            $table->date('rec_date')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->string('mobile')->nullable();
            $table->text('remark')->nullable();
            $table->string('grno')->nullable();
            $table->date('grdate')->nullable();
            $table->string('add1')->nullable();
            $table->string('add2')->nullable();
            $table->string('add3')->nullable();
            $table->string('add4')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('transport')->nullable();
            $table->string('interstate')->nullable();
            $table->decimal('add_total', 15, 2)->nullable();
            $table->decimal('less_total', 15, 2)->nullable();
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
        Schema::dropIfExists('sale_registers');
    }
};
