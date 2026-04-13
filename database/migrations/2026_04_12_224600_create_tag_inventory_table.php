<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->string('batch_code', 50)->nullable()->comment('Código do lote de etiquetas');
            $table->unsignedInteger('initial_quantity');
            $table->unsignedInteger('current_quantity');
            $table->decimal('unit_cost', 8, 2)->nullable();
            $table->unsignedInteger('minimum_stock')->default(0)->comment('Alerta quando abaixo deste valor');
            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_inventory');
    }
};
