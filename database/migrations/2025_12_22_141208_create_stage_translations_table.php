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
        Schema::create('stage_translations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
    $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
    $table->string('name', 255)->nullable(); // nullable allows “clear translation”
    $table->timestamps();

    $table->unique(['stage_id', 'language_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_translations');
    }
};
