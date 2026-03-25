// database/migrations/0001_01_01_000008_create_subscription_plans_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_period', ['monthly', 'yearly'])->default('monthly');
            $table->integer('max_products')->default(10);
            $table->integer('max_images_per_product')->default(5);
            $table->boolean('featured_listing')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->decimal('commission_rate', 5, 2)->default(10);
            $table->json('features')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->string('razorpay_subscription_id')->nullable();
            $table->json('payment_data')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
            
            $table->index(['vendor_profile_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};