// database/migrations/0001_01_01_000011_create_product_reviews_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('rating')->unsigned();
            $table->text('review')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['product_id', 'is_approved', 'rating']);
            $table->index(['user_id', 'product_id']);
        });

        Schema::create('review_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_review_id')->constrained()->cascadeOnDelete();
            $table->string('media_path');
            $table->string('media_url')->nullable();
            $table->string('media_type')->default('image');
            $table->timestamps();
        });

        Schema::create('review_helpful_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_review_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['product_review_id', 'user_id'], 'review_vote_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_helpful_votes');
        Schema::dropIfExists('review_media');
        Schema::dropIfExists('product_reviews');
    }
};