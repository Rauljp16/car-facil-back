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
        if (!Schema::hasTable('coches')) {
            Schema::create('coches', function (Blueprint $table) {
                $table->id();
                $table->string('marca');
                $table->string('modelo');
                $table->string('cambio');
                $table->string('combustible');
                $table->integer('motor');
                $table->integer('cv');
                $table->integer('plazas');
                $table->integer('puertas');
                $table->integer('anio');
                $table->integer('km');
                $table->decimal('precio', 10, 2)->nullable();
                $table->json('foto')->nullable();
                $table->timestamps();
            });
        }
      }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coches');
    }
};
