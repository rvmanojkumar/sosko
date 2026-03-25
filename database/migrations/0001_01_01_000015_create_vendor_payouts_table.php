// database/migrations/0001_01_01_000015_create_vendor_payouts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('razorpay_transfer_id')->nullable();
            $table->json('transfer_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_profile_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_payouts');
    }
};