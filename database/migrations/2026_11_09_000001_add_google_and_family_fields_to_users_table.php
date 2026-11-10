<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Login hanya lewat Google: password tidak dipakai.
            $table->string('password')->nullable()->change();

            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('google_id');

            // Ruang Orang Tua
            $table->string('parent_call', 20)->nullable();     // "Bunda", "Ayah", ...
            $table->string('note_message', 200)->nullable();   // pesan semangat
            $table->string('note_promise', 100)->nullable();   // janji kecil hari ini
            $table->boolean('sound')->default(true);

            // Persetujuan orang tua (UU PDP)
            $table->timestamp('consent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'avatar', 'parent_call', 'note_message', 'note_promise', 'sound', 'consent_at']);
        });
    }
};
