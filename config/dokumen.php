<?php

/*
|--------------------------------------------------------------------------
| Daftar dokumen akreditasi di Google Drive
|--------------------------------------------------------------------------
| Kiri : nama dokumen persis seperti yang muncul di jawaban chatbot
|        (nama file di Pinecone/n8n, tanpa ".pdf").
| Kanan: ID file Google Drive.
|
| Cara ambil ID: buka file di Drive -> Bagikan -> Salin link, contoh
|   https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUvWxYz/view?usp=sharing
| maka ID-nya adalah bagian di antara /d/ dan /view :  1AbCdEfGhIjKlMnOpQrStUvWxYz
|
| Dokumen yang belum didaftarkan di sini tetap tampil sebagai teks biasa
| (tidak dibuatkan link).
*/

return [
    'drive' => [
        '1_7_Standar_Pengelolaan_Pembelajaran' => '1RHmycUtvHa6Ef77ksgm-R9Nge8PYZM8j',
        '1_4_Standar_Penilaian_Pembelajaran'   => '1zAmAcSed0w7HLsaiCy1TFbx0t4uXLS7y',
        '1_1_Standar_Kompetensi_Lulusan'   => '1dpmccFw8cOhKy5msFs1L0zp3pzOsIY9X',
        '1_2_Standar_Isi_Pembelajaran'   => '12n8rgJFDitHirr-R2lIRgNn-aZvMSXXT',
        '1_3_Standar_Proses_Pembelajaran'   => '1P-lybZqY89PKsmUm2L_X7J_AGruP_oxh',
        '1_5_Standar_Dosen_Tendik'   => '1wpbAOKWpiUvx7KsJQeUaCHB0WYp6IoGh',
        '1_6_Standar_Sarpras_Pembelajaran'   => '1Gf2EQXEN41WvRob9Dkqd5ARKPd-fX5jR',
        '1_8_Standar_Pembiayaan_Pembelajaran'   => '1KA5vDO4TZMpSEJnNVVYax9Q6hJWYgG27',
        '2_1_Standar_Hasil_Penelitian'   => '1-pojmFBp1a6qzaCyAedN8rWt6jprTeHK',
        '2_2_Standar_Isi_Penelitian'   => '1XVcAKIQnoeo27BjLG6GRmGuZAyrvEz8f',
    ],
];