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
        Schema::create('calculadora_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('empresa')->nullable();
            $table->json('datos_boleta')->nullable();
            $table->json('resultado')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'error'])
                  ->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculadora_solicitudes');
    }
};
