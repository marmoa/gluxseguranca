<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traceability_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('digit_count');   // 4 ou 6
            $table->unsignedInteger('range_start');       // ex: 100001
            $table->unsignedInteger('range_end');         // ex: 599999
            $table->unsignedInteger('last_used')->default(0); // último código emitido
            $table->string('label', 100)->nullable();     // rótulo amigável ex: "Série A"
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('digit_count');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traceability_settings');
    }
};
