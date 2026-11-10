<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu sesi main = satu halaman di Buku Kenangan.
        Schema::create('plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('level', 4);          // tk, k1..k5
            $table->string('topic', 40);         // key topik, mis. "jam", "ch_campur", "iksc"
            $table->unsignedTinyInteger('stars');
            $table->unsignedSmallInteger('items')->default(0);
            $table->unsignedSmallInteger('misses')->default(0);
            $table->unsignedSmallInteger('score')->nullable();   // skor berpoin (IKSC)
            $table->boolean('together')->default(false);
            $table->unsignedSmallInteger('by_kid')->default(0);
            $table->unsignedSmallInteger('by_parent')->default(0);
            $table->unsignedTinyInteger('stamp')->nullable();     // 0..3 = Bangga/Sayang/Hebat/Semangat
            $table->timestamp('played_at')->useCurrent();
            $table->timestamps();

            $table->index(['child_id', 'played_at']);
            $table->index(['child_id', 'level', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plays');
    }
};
