<?php

namespace Database\Seeders;

use App\Models\QuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi bank soal tetap dari database/seeders/data/*.json.
 * Aman dijalankan ulang: soal di bank yang sama diganti seluruhnya.
 *
 * IKSC default TIDAK publik (naskah & gambar milik penyelenggara).
 * Set ASAHI_IKSC_PUBLIC=true di .env hanya jika sudah ada izin.
 */
class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(database_path('seeders/data/*.json')) as $file) {
            $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

            $public = str_starts_with($data['slug'], 'IKSC')
                ? (bool) config('asahi.iksc_public')
                : (bool) ($data['public'] ?? true);

            DB::transaction(function () use ($data, $public) {
                $bank = QuestionBank::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'title' => $data['title'],
                        'level' => $data['level'] ?? null,
                        'ordered' => (bool) ($data['ordered'] ?? false),
                        'is_public' => $public,
                        'assets' => $data['assets'] ?? null,
                    ],
                );

                $bank->questions()->delete();
                foreach ($data['questions'] as $i => $q) {
                    $fmt = $q['fmt'];
                    unset($q['fmt']);
                    $bank->questions()->create(['position' => $i + 1, 'fmt' => $fmt, 'payload' => $q]);
                }
            });

            $this->command?->info(sprintf('%s: %d soal%s', $data['slug'], count($data['questions']), $public ? '' : ' (tersembunyi)'));
        }
    }
}
