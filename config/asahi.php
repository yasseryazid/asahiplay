<?php

return [
    // Naskah & gambar IKSC milik penyelenggara. Aktifkan hanya jika sudah ada izin.
    'iksc_public' => (bool) env('ASAHI_IKSC_PUBLIC', false),
];
