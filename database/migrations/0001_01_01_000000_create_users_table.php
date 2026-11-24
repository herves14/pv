<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La table filieres est créée ici, avant users, car un chef de
     * département est rattaché à une filière (RG03).
     */
    public function up(): void
    {
        Schema::create('filieres', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();      // RG07, ex. « GRH »
            $table->string('libelle', 150);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 30);                 // RG01 : admin | chef_departement | expedition
            $table->foreignId('filiere_id')             // RG03 : rempli seulement pour un chef
                ->nullable()
                ->constrained('filieres')
                ->restrictOnDelete();
            $table->boolean('actif')->default(true);    // RG05
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'filiere_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('filieres');
    }
};
