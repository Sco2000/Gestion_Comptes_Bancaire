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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('telephone')->unique();
            $table->boolean('actif')->default(true);
            $table->string('nci')->nullable()->unique();
            $table->string('login')->nullable()->unique()->after('email');
            $table->string('plain_password')->nullable()->after('password');
            $table->dropColumn(['name', 'email_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'telephone', 'actif', 'nci', 'login', 'plain_password']);
            $table->string('name');
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
