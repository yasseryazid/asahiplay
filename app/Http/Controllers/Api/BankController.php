<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    /** Bank soal tetap. Hanya yang is_public yang bisa diambil. */
    public function show(string $slug): JsonResponse
    {
        $bank = QuestionBank::where('slug', $slug)->where('is_public', true)->firstOrFail();

        return response()
            ->json([
                'slug' => $bank->slug,
                'title' => $bank->title,
                'ordered' => $bank->ordered,
                'assets' => $bank->assets ?? (object) [],
                'questions' => $bank->questions->map->toAsahi()->values(),
            ])
            ->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Gambar pendukung bank soal. Disimpan di storage/app/banks/{slug}/ (bukan public/)
     * supaya ikut tersembunyi kalau bank-nya tidak publik (mis. IKSC sebelum ada izin).
     */
    public function asset(string $slug, string $file)
    {
        QuestionBank::where('slug', $slug)->where('is_public', true)->firstOrFail();

        $path = storage_path('app/banks/' . strtolower($slug) . '/' . basename($file));
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
    }
}
