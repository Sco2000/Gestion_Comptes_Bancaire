<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the existing enum constraint
        DB::statement("ALTER TABLE comptes DROP CONSTRAINT IF EXISTS comptes_statut_check");

        // Update status 'archive' to 'supprimé' in main database
        DB::table('comptes')->where('statut', 'archive')->update(['statut' => 'supprimé']);

        // Recreate the enum with new values
        DB::statement("ALTER TABLE comptes ADD CONSTRAINT comptes_statut_check CHECK (statut IN ('actif', 'bloque', 'supprimé'))");

        // Update status 'archive' to 'supprimé' in Neon database
        DB::connection('neon')->statement("ALTER TABLE comptes DROP CONSTRAINT IF EXISTS comptes_statut_check");
        DB::connection('neon')->table('comptes')->where('statut', 'archive')->update(['statut' => 'supprimé']);
        DB::connection('neon')->statement("ALTER TABLE comptes ADD CONSTRAINT comptes_statut_check CHECK (statut IN ('actif', 'bloque', 'supprimé'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, drop the existing enum constraint
        DB::statement("ALTER TABLE comptes DROP CONSTRAINT IF EXISTS comptes_statut_check");

        // Revert status 'supprimé' to 'archive' in main database
        DB::table('comptes')->where('statut', 'supprimé')->update(['statut' => 'archive']);

        // Recreate the enum with old values
        DB::statement("ALTER TABLE comptes ADD CONSTRAINT comptes_statut_check CHECK (statut IN ('actif', 'bloque', 'archive'))");

        // Revert status 'supprimé' to 'archive' in Neon database
        DB::connection('neon')->statement("ALTER TABLE comptes DROP CONSTRAINT IF EXISTS comptes_statut_check");
        DB::connection('neon')->table('comptes')->where('statut', 'supprimé')->update(['statut' => 'archive']);
        DB::connection('neon')->statement("ALTER TABLE comptes ADD CONSTRAINT comptes_statut_check CHECK (statut IN ('actif', 'bloque', 'archive'))");
    }
};
