<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('neon')->create('comptes', function (Blueprint $table) {
            $table->uuid('id'); // pas de clé primaire
            $table->uuid('client_id');
            $table->string('numero_compte');
            $table->enum('type', ['epargne', 'cheque']);
            $table->decimal('solde', 15, 2);
            $table->dateTime('date_creation');
            $table->enum('statut', ['actif', 'bloque', 'supprimé'])->default('actif');
            $table->date('date_debut_blocage')->nullable();
            $table->date('date_fin_blocage')->nullable();
            $table->timestamps();
        });

         // Ajouter les contraintes uniques **hors transaction**
        DB::connection('neon')->statement('CREATE UNIQUE INDEX comptes_id_unique ON comptes(id);');
        DB::connection('neon')->statement('CREATE UNIQUE INDEX comptes_numero_compte_unique ON comptes(numero_compte);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
