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
        Schema::create('layer_group_member', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->foreignId('layer_group_id')->constrained();
            $table->foreignId('layer_id')->constrained();
            $table->userTimestamps();

            // fk
            $table->foreignUserTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layer_group_member');
    }
};
