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
        Schema::create('color_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->foreignId('color_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('label', 30);
            $table->string('color', 30);
            $table->userTimestamps();

            // fk
            $table->foreignUserTimestamps();

            // uk
            $table->unique(['color_group_id', 'label'], 'color_items_unique_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('color_items');
    }
};
