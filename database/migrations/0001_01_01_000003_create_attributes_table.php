// database/migrations/0001_01_01_000003_create_attributes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('select');
            $table->string('display_type')->default('dropdown');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_global')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('color_code')->nullable();
            $table->string('image')->nullable();
            $table->string('swatch_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['attribute_id', 'sort_order']);
        });

        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attribute_group_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attribute_group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('attribute_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['attribute_group_id', 'attribute_id'], 'group_attribute_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_group_mappings');
        Schema::dropIfExists('attribute_groups');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};