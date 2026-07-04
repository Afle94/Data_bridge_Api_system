<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_masters')) {
            return;
        }

        Schema::create('account_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('user_code')->nullable()->index();
            $table->string('acno')->nullable()->index();
            $table->string('hacno')->nullable()->index();
            $table->string('achead')->nullable()->index();
            $table->decimal('opening', 16, 2)->nullable();
            $table->string('open_type', 1)->nullable();
            $table->decimal('current', 16, 2)->nullable();
            $table->string('current_type', 1)->nullable();
            $table->string('add1')->nullable();
            $table->string('add2')->nullable();
            $table->string('add3')->nullable();
            $table->string('add4')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->string('category')->nullable()->index();
            $table->integer('cr_days')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('contact')->nullable();
            $table->string('mobile')->nullable();
            $table->string('pan_no')->nullable();
            $table->date('pan_date')->nullable();
            $table->string('state')->nullable()->index();
            $table->decimal('on_ac_amt', 15, 2)->nullable();
            $table->string('on_ac_type', 1)->nullable();
            $table->string('sales_agent')->nullable();
            $table->decimal('cr_limit', 15, 2)->nullable();
            $table->string('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_masters');
    }
};
