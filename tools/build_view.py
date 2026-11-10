"""
Membangun resources/views/app.blade.php dari file Asahi (index.html versi offline).
- Membuang data bank soal yang dibundel (PAI, IKSC) -> di versi server soal diambil dari /api/banks.
- Menyuntikkan window.ASAHI (data awal dari Laravel) sebelum script utama.

Pakai:  python3 tools/build_view.py path/ke/index.html
"""
import re, sys, pathlib

src = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else 'resources/asahi/index.html').read_text(encoding='utf-8')
root = pathlib.Path(__file__).resolve().parent.parent

def strip(s, a, b):
    out, n = re.subn(re.escape(a) + r'.*?' + re.escape(b) + r'\n?', '', s, flags=re.S)
    assert n == 1, f'marker {a} tidak ditemukan'
    return out

s = strip(src, '/*BANKDATA*/', '/*END-BANKDATA*/')
s = strip(s, '/*IKSCDATA*/', '/*END-IKSCDATA*/')

MAIN = '<script>\n/* ---------- helpers ---------- */'
assert s.count(MAIN) == 1
before, after = s.split(MAIN)
before = before.replace('<meta name="viewport"', '<meta name="csrf-token" content="{{ csrf_token() }}">\n<meta name="viewport"', 1)

# Blade: bagian statis dibungkus @verbatim supaya {{ }} / @ di JS/CSS tidak diproses.
head, rest = before.split('<meta name="csrf-token" content="{{ csrf_token() }}">', 1)
blade = (
    '@verbatim\n' + head + '@endverbatim\n'
    '<meta name="csrf-token" content="{{ csrf_token() }}">\n'
    '@verbatim\n' + rest + '@endverbatim\n'
    '<script>window.ASAHI = {!! json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!};</script>\n'
    '@verbatim\n' + MAIN + after + '@endverbatim\n'
)
out = root / 'resources/views/app.blade.php'
out.write_text(blade, encoding='utf-8')

# Versi uji (dipakai mock server): placeholder __BOOT__
(root / 'tools/app.test.html').write_text(before.replace('{{ csrf_token() }}', 'test') + '<script>window.ASAHI = __BOOT__;</script>\n' + MAIN + after, encoding='utf-8')
print(f'{out} ({len(blade)//1024} KB, offline {len(src)//1024} KB)')
