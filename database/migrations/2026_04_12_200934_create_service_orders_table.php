<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique()->comment('Número da OS ex: OS-2026-0001');
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('client_contract_id')->nullable()->constrained('client_contracts')->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->comment('Responsável pela OS');
            $table->string('status', 30)->default('open')->comment('ServiceOrderStatus enum');
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('address')->nullable()->comment('Logradouro, bairro, CEP do local do serviço');
            $table->decimal('temperature', 5, 1)->nullable()->comment('Temperatura ambiente em °C');
            $table->decimal('humidity', 5, 1)->nullable()->comment('Umidade relativa em %');
            $table->text('notes')->nullable();
            $table->date('scheduled_at')->nullable()->comment('Data prevista para execução');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('billed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('client_id');
            $table->index('user_id');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
