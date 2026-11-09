<?php

namespace App\Http\Controllers;

use App\Models\CapaianIndikator;
use App\Models\Indikator;
use App\Models\Periode;
use App\Models\User;
use App\Services\RingkasanCapaian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /** Halaman awal berbeda per peran (dev.md §6.1–6.4). */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $periode = Periode::aktif();

        return match ($user->peranUtama()) {
            'administrator'  => $this->statusSistem($periode),
            'kaprodi'        => $this->dashboardKaprodi($periode, $user),
            'tim_akreditasi' => $this->berandaKerja($periode, $user),
            'tim_gkm'        => $this->antreanVerifikasi($periode, $user),
            default          => view('beranda.tanpa-peran'),
        };
    }

    // §6.1 — Status Sistem
    private function statusSistem(?Periode $periode)
    {
        // Indikator berpola B/C/D/E yang resolver-nya belum ada (§7)
        $tanpaResolver = Indikator::where('pola', '!=', 'A')->get(['id', 'sumber_data'])
            ->filter(fn ($i) => !$i->sumber_data
                || !class_exists('App\\Services\\Resolver\\' . Str::studly($i->sumber_data) . 'Resolver'))
            ->count();

        return view('beranda.administrator', [
            'periode'        => $periode,
            'penggunaAktif'  => User::where('is_aktif', true)->count(),
            'penggunaTotal'  => User::count(),
            'periodeTerkunci' => Periode::where('is_terkunci', true)->count(),
            'tanpaResolver'  => $tanpaResolver,
            'totalIndikator' => Indikator::count(),
            'hitungTerakhir' => CapaianIndikator::max('dihitung_pada'),
            'errorLog'       => $this->ringkasanErrorLog(),
        ]);
    }

    // §6.2 — Dashboard Capaian
    private function dashboardKaprodi(?Periode $periode, User $user)
    {
        $ringkasan = (new RingkasanCapaian($periode, $user->prodi_id))->perStandar();

        return view('beranda.kaprodi', [
            'periode'          => $periode,
            'kartu'            => $ringkasan['kartu'],
            'dibawahTarget'    => $ringkasan['dibawah_target'],
            'antreanSetuju'    => CapaianIndikator::where('status', 'terverifikasi')->count(),
        ]);
    }

    // §6.3 — Beranda Kerja
    private function berandaKerja(?Periode $periode, User $user)
    {
        $ringkasan = new RingkasanCapaian($periode, $user->prodi_id);
        $capaian = $ringkasan->capaian();

        $belumAdaData = Indikator::with('standar')
            ->where('is_ruang_lingkup_prodi', true)
            ->whereNotIn('id', $capaian->keys())
            ->get()
            ->sortBy(fn ($i) => $i->standar->kode . '.' . str_pad($i->nomor, 3, '0', STR_PAD_LEFT))
            ->values();

        $perluRevisi = CapaianIndikator::with('indikator.standar', 'verifikasi')
            ->whereIn('status', ['ditolak', 'dikembalikan'])
            ->when($periode, fn ($q) => $q->where('periode_id', $periode->id))
            ->latest('updated_at')
            ->get();

        return view('beranda.tim-akreditasi', compact('periode', 'belumAdaData', 'perluRevisi'));
    }

    // §6.4 — Antrean Verifikasi
    private function antreanVerifikasi(?Periode $periode, User $user)
    {
        $antrean = CapaianIndikator::with('indikator.standar', 'periode')
            ->where('status', 'diajukan')
            ->oldest('updated_at')
            ->get();

        $target = (new RingkasanCapaian($periode, $user->prodi_id))->target();

        return view('beranda.tim-gkm', compact('periode', 'antrean', 'target'));
    }

    /** Jumlah error di laravel.log 24 jam terakhir + waktu error terakhir. */
    private function ringkasanErrorLog(): array
    {
        $path = storage_path('logs/laravel.log');
        if (!is_file($path)) {
            return ['jumlah' => 0, 'terakhir' => null];
        }

        // Baca bagian akhir file saja (maks. 512 KB) supaya tetap cepat
        $ukuran = filesize($path);
        $isi = file_get_contents($path, false, null, max(0, $ukuran - 524288));
        preg_match_all('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(?:ERROR|CRITICAL|ALERT|EMERGENCY):/m', $isi, $m);

        $batas = now()->subDay()->format('Y-m-d H:i:s');
        $baru = array_filter($m[1], fn ($t) => $t >= $batas);

        return ['jumlah' => count($baru), 'terakhir' => $baru ? end($baru) : null];
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
