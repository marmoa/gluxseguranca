<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->comment('Ano de referência para reset do contador');
            $table->unsignedInteger('last_number')->default(0)->comment('Último número sequencial usado no ano');
            $table->string('prefix', 10)->default('OS');
            $table->timestamps();

            $table->unique('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_numbers');
    }
};
