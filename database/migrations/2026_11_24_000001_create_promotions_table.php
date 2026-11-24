<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained('filieres')->restrictOnDelete(); // RG08, RG11
            $table->string('libelle', 100);   // ex. « Promotion 2022-2025 »
            $table->timestamps();

            $table->unique(['filiere_id', 'libelle']); // RG08
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
