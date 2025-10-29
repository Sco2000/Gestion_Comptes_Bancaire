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
        Schema::connection('neon')->create('admins', function (Blueprint $table) {
            $table->uuid('id'); // pas de clé primaire
            $table->uuid('user_id');
            $table->string('matricule');
            $table->timestamps();
        });

         // Ajouter les contraintes uniques **hors transaction**
        DB::connection('neon')->statement('CREATE UNIQUE INDEX admins_id_unique ON admins(id);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
