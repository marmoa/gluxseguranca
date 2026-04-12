<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('abbreviation', 2)->unique();
            $table->timestamps();

            $table->index('abbreviation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
