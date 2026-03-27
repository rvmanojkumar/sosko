<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->string('account_holder_name');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc_code');
            $table->text('branch_address')->nullable();
            $table->string('upi_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('razorpay_fund_account_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_profile_id', 'is_default']);
            $table->unique(['vendor_profile_id', 'account_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_bank_accounts');
    }
};