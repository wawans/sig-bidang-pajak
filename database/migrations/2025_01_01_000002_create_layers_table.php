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
        Schema::create('layers', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->string('name', 225);
            $table->string('namespace', 225);
            $table->string('datasource', 30)->nullable();
            $table->string('geometry', 30)->nullable();
            $table->string('geometry_type', 30)->nullable();
            $table->json('properties')->nullable();
            $table->unsignedTinyInteger('zindex')->default(1);
            $table->boolean('writeable')->default(false);
            $table->boolean('autoload')->default(false);
            $table->unsignedBigInteger('default_style_id')->nullable();
            $table->unsignedBigInteger('select_style_id')->nullable();
            $table->userTimestamps();

            // fk
            $table->foreign('default_style_id')->references('id')->on('styles')->nullOnDelete();
            $table->foreign('select_style_id')->references('id')->on('styles')->nullOnDelete();
            $table->foreignUserTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layers');
    }
};
