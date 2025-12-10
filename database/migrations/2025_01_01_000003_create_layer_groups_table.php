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
        Schema::create('layer_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->string('name', 225);
            $table->unsignedBigInteger('parent_layer_group_id')->nullable()->index();
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
        Schema::dropIfExists('layer_groups');
    }
};
