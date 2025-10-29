<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::connection('neon')->create('users', function (Blueprint $table) {
            $table->uuid('id'); // pas de clé primaire
            $table->string('nom');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telephone');
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

         // Ajouter les contraintes uniques **hors transaction**
        DB::connection('neon')->statement('CREATE UNIQUE INDEX users_id_unique ON users(id);');
        DB::connection('neon')->statement('CREATE UNIQUE INDEX users_email_unique ON users(email);');
    }

    
    public function down(): void
    {
        Schema::connection('neon')->dropIfExists('users');
    }
};