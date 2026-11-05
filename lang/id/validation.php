<?php

// Pesan validasi Bahasa Indonesia (aturan yang dipakai SIAKRED). Aturan lain jatuh ke bahasa Inggris.
return [
    'after_or_equal' => ':Attribute harus tanggal yang sama atau setelah :date.',
    'boolean'        => ':Attribute harus bernilai ya atau tidak.',
    'date'           => ':Attribute bukan tanggal yang valid.',
    'email'          => ':Attribute harus berupa alamat email yang valid.',
    'exists'         => ':Attribute yang dipilih tidak valid.',
    'file'           => ':Attribute harus berupa berkas.',
    'in'             => ':Attribute yang dipilih tidak valid.',
    'integer'        => ':Attribute harus berupa bilangan bulat.',
    'numeric'        => ':Attribute harus berupa angka.',
    'mimes'          => ':Attribute harus berupa berkas bertipe: :values.',
    'required'       => ':Attribute wajib diisi.',
    'required_with'  => ':Attribute wajib diisi bila :values diisi.',
    'string'         => ':Attribute harus berupa teks.',
    'unique'         => ':Attribute sudah digunakan.',
    'uploaded'       => ':Attribute gagal diunggah.',
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'file'    => ':Attribute tidak boleh lebih dari :max kilobyte.',
        'string'  => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'file'    => ':Attribute minimal :min kilobyte.',
        'string'  => ':Attribute minimal :min karakter.',
    ],
    'attributes' => [],
];
