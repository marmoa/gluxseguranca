<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_order_item_id')->constrained('service_order_items')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            // Código de rastreabilidade
            $table->string('traceability_code', 10)->nullable()->unique();
            $table->unsignedTinyInteger('digit_count')->nullable();

            // Resultado
            $table->string('result', 20)->default('pending'); // InspectionResult enum
            $table->string('rejection_category', 30)->nullable(); // RejectionCategory enum
            $table->text('rejection_notes')->nullable();

            // Validade
            $table->date('expires_at')->nullable();

            // Ordem dentro do lote (para exibição)
            $table->unsignedSmallInteger('batch_sequence')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_order_id', 'result']);
            $table->index(['item_id', 'result']);
            $table->index('expires_at');
            $table->index('traceability_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
