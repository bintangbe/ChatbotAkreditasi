<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Models\Indikator;
use App\Models\Standar;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'standar' => ['nullable', 'integer'],
            'pola'    => ['nullable', 'in:' . implode(',', array_keys(Indikator::POLA))],
            'lingkup' => ['nullable', 'in:prodi,luar'],
            'q'       => ['nullable', 'string', 'max:100'],
        ]);

        $indikator = Indikator::query()
            ->with('standar')
            ->join('standar', 'standar.id', '=', 'indikator.standar_id')
            ->select('indikator.*')
            ->when($filter['standar'] ?? null, fn ($q, $v) => $q->where('indikator.standar_id', $v))
            ->when($filter['pola'] ?? null, fn ($q, $v) => $q->where('indikator.pola', $v))
            ->when(($filter['lingkup'] ?? null) === 'prodi', fn ($q) => $q->where('indikator.is_ruang_lingkup_prodi', true))
            ->when(($filter['lingkup'] ?? null) === 'luar', fn ($q) => $q->where('indikator.is_ruang_lingkup_prodi', false))
            ->when($filter['q'] ?? null, fn ($q, $v) => $q->where('indikator.pernyataan', 'like', '%' . addcslashes($v, '%_\\') . '%'))
            ->orderBy('standar.kode')
            ->orderBy('indikator.nomor')
            ->paginate(25)
            ->withQueryString();

        return view('referensi.indikator', [
            'indikator'     => $indikator,
            'daftarStandar' => Standar::orderBy('kode')->get(['id', 'kode', 'nama']),
            'filter'        => $filter,
        ]);
    }
}
