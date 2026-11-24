<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proces_verbaux', function (Blueprint $table) {
            $table->id();
            // RG12 : un PV = une promotion (donc une filière), une année, un semestre.
            // restrictOnDelete applique RG11 : on ne supprime pas un référentiel utilisé.
            $table->foreignId('promotion_id')->constrained('promotions')->restrictOnDelete();
            $table->foreignId('annee_academique_id')->constrained('annees_academiques')->restrictOnDelete();
            $table->foreignId('semestre_id')->constrained('semestres')->restrictOnDelete();
            $table->string('session', 20)->default('normale'); // RG13 : normale | rattrapage
            $table->date('date_deliberation')->nullable();

            // RG14 : le PDF est rangé dans le disque privé "local" sous un nom généré.
            $table->string('fichier_chemin');
            $table->string('fichier_nom_original');
            $table->unsignedBigInteger('fichier_taille');     // en octets
            $table->text('observation')->nullable();

            // RG16 : qui a téléversé ; created_at = date de téléversement.
            $table->foreignId('televerse_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            // RG13 : au plus un PV par (promotion, année, semestre, session).
            $table->unique(
                ['promotion_id', 'annee_academique_id', 'semestre_id', 'session'],
                'pv_unique_combinaison'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proces_verbaux');
    }
};
