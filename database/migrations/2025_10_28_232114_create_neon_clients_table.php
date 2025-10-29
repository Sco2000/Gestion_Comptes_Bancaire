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
        Schema::connection('neon')->create('clients', function (Blueprint $table) {
            $table->uuid('id'); // pas de clé primaire
            $table->uuid('user_id');
            $table->string('prenom');
            $table->string('nom');
            $table->string('email');
            $table->string('telephone');
            $table->text('adresse');
            $table->string('nci');
            $table->date('date_naissance');
            $table->timestamps();
        });

         // Ajouter les contraintes uniques **hors transaction**
        DB::connection('neon')->statement('CREATE UNIQUE INDEX clients_id_unique ON clients(id);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
