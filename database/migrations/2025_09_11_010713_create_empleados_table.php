<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Opción A: usar enum (si tu BD lo soporta)
            $table->enum('role', ['mesero', 'cajero', 'cocinero'])->default('mesero');
            // Opción B (más compatible): usar string y validar desde Laravel
            // $table->string('role');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
