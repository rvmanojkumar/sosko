// database/migrations/0001_01_01_000014_create_vendor_earnings_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_earnings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('order_amount', 15, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('vendor_amount', 15, 2);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_profile_id', 'status']);
            $table->index(['order_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_earnings');
    }
};