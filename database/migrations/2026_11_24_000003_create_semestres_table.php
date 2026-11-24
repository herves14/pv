<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semestres', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();   // RG10 : S1 … S6
            $table->string('libelle', 50);         // « Semestre 1 »
            $table->unsignedTinyInteger('ordre');  // ordre d'affichage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semestres');
    }
};
