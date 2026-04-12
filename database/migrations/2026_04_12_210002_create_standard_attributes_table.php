<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template de atributos por padrão (pré-seleciona ao criar item)
        Schema::create('standard_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['standard_id', 'attribute_id']);
            $table->index(['standard_id', 'sort_order']);
        });

        // Valores padrão do template (pré-seleciona valores ao criar item)
        Schema::create('standard_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_attribute_id')->constrained('standard_attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['standard_attribute_id', 'attribute_value_id'], 'std_attr_val_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_attribute_values');
        Schema::dropIfExists('standard_attributes');
    }
};
