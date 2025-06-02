<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('artistas')) {
            Schema::create('artistas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');  // Cambiado de 'name' a 'nombre' para coincidir con el seeder
                $table->text('biografia')->nullable();
                $table->string('pais')->nullable();  // Añadido campo 'pais' que usa el seeder
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('artistas');
    }
};