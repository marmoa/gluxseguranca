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
        Schema::create('tag_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_inventory_id')->constrained('tag_inventory')->cascadeOnDelete();
            $table->foreignId('distributed_to')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('distributed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_distributions');
    }
};
