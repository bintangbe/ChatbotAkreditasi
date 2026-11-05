<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Models\Standar;

class StandarController extends Controller
{
    public function index()
    {
        $standar = Standar::withCount([
                'indikator',
                'indikator as indikator_prodi_count' => fn ($q) => $q->where('is_ruang_lingkup_prodi', true),
            ])
            ->orderBy('kode')
            ->get();

        return view('referensi.standar', compact('standar'));
    }
}
