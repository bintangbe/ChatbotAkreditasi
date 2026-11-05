<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Standar;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard()
    {
        $standar = Standar::where('is_berlaku', true)
            ->withCount('indikator')
            ->orderBy('kode')
            ->get();

        return view('pages.dashboard', [
            'standar'        => $standar,
            'totalIndikator' => $standar->sum('indikator_count'),
            'periodeAktif'   => Periode::aktif(),
        ]);
    }

    // Halaman menu yang belum dibangun (lihat config/siakred.php)
    public function placeholder(Request $request)
    {
        return view('pages.placeholder', [
            'judul' => $request->route()->defaults['judul'] ?? 'Halaman',
            'grup'  => $request->route()->defaults['grup'] ?? null,
        ]);
    }
}
