<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();

            // Para atributos tipo 'text': valor digitado
            $table->text('text_value')->nullable();

            // Para atributos tipo 'select': ID do valor selecionado
            $table->foreignId('attribute_value_id')
                ->nullable()
                ->constrained('attribute_values')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['inspection_id', 'attribute_id']);
            $table->index(['inspection_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_values');
    }
};
