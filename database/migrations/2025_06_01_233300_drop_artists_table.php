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
        Schema::dropIfExists('artistas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('artistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('biografia')->nullable();
            $table->string('pais')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();
        });
    }
};
