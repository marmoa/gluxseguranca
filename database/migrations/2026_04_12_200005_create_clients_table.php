<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);           // razão social
            $table->string('trade_name', 150)->nullable();  // nome fantasia
            $table->string('cnpj', 18)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->foreignId('standard_id')->nullable()->constrained()->nullOnDelete();

            // Endereço
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 200)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('zip_code', 10)->nullable();

            // Responsável
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_mobile', 20)->nullable();

            // Dados adicionais
            $table->string('segment', 100)->nullable();   // segmento de atuação
            $table->string('cost_center', 50)->nullable(); // centro de custo
            $table->string('base', 100)->nullable();       // base/filial

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('cnpj');
        });

        // Agora adiciona FK de users → clients
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
        Schema::dropIfExists('clients');
    }
};
