<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->unsignedTinyInteger('digit_count')->default(6); // 4 ou 6
            $table->unsignedSmallInteger('expiration_months')->default(12); // meses de validade
            $table->string('photo_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['standard_id', 'is_active']);
        });

        // Pivot: item ↔ attribute
        Schema::create('item_attribute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'attribute_id']);
        });

        // Pivot: item_attribute ↔ attribute_value (valores habilitados por item)
        Schema::create('item_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'attribute_id', 'attribute_value_id'], 'item_attr_val_unique');
        });

        // Pivot: item ↔ norm
        Schema::create('item_norm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('norm_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'norm_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_norm');
        Schema::dropIfExists('item_attribute_value');
        Schema::dropIfExists('item_attribute');
        Schema::dropIfExists('items');
    }
};
