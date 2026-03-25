// database/migrations/0001_01_01_000013_create_product_questions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->boolean('is_answered')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['product_id', 'is_answered']);
        });

        Schema::create('product_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_question_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('vendor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('answer');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['product_question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_answers');
        Schema::dropIfExists('product_questions');
    }
};