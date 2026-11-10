<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bank soal tetap (PAI, IKSC, dst). Soal hitungan/olimpiade dibuat otomatis di frontend.
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();   // dipakai frontend: PAI_NABI, PAI_RUKUN, IKSC
            $table->string('title');
            $table->string('level', 4)->nullable();
            $table->boolean('ordered')->default(false);   // true = urutan & jumlah soal tetap (naskah lomba)
            $table->boolean('is_public')->default(true);  // false = disembunyikan (mis. menunggu izin hak cipta)
            $table->json('assets')->nullable();           // gambar/cerita pendukung
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('fmt', 12);      // pg, kata, urut, jodoh, multi, ...
            $table->json('payload');        // isi soal sesuai format (lihat README)
            $table->timestamps();

            $table->index(['question_bank_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
