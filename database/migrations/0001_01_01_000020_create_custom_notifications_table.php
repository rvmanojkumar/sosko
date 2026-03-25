// database/migrations/0001_01_01_000020_create_custom_notifications_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only create if we need additional fields
        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->string('type')->nullable();
                $table->json('data')->nullable();
                $table->string('image')->nullable();
                $table->string('link')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'is_read']);
                $table->index(['created_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_notifications');
    }
};