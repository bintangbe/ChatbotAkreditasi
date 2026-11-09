<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DokumenBukti;
use App\Models\Periode;
use App\Services\DampakCapaian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/**
 * CRUD generik untuk semua modul master (dev.md §4.1–4.3).
 *
 * Turunan cukup mengisi properti di bawah. Definisi field:
 *   'kolom' => [
 *     'label'  => 'Nama',
 *     'type'   => text|email|number|decimal|date|year|textarea|select|boolean,
 *     'rules'  => 'required|string|max:150',   // {id} diganti id record (untuk unique)
 *     'options'=> ['nilai' => 'Label'],        // untuk select statis
 *     'relasi' => [Model::class, 'kolomLabel'],// untuk select dari tabel lain
 *     'bantuan'=> 'teks kecil di bawah input',
 *   ]
 * Relasi anak: 'kunci' => ['judul', 'model', 'entitas', 'fk', 'kolom' => [...], 'fields' => [...]]
 */
abstract class BaseCrudController extends Controller
{
    protected string $model;
    protected string $entitas;          // nama tabel, juga kunci di config/permissions.php
    protected string $judul;
    protected string $routeBase;        // nama route menu, mis. 'master.pegawai'
    protected string $kolomJudul = 'nama';
    protected string $urutan = 'nama';
    protected array $kolomTabel = [];
    protected array $kolomCari = [];
    protected array $filter = [];
    protected array $fields = [];
    protected array $relasiAnak = [];
    protected array $with = [];

    // ===== Route =====

    public static function routes(string $url, string $nama): void
    {
        $c = static::class;
        Route::get($url, [$c, 'index'])->name($nama);
        Route::get("{$url}/create", [$c, 'create'])->name("{$nama}.create");
        Route::post($url, [$c, 'store'])->name("{$nama}.store");
        Route::prefix("{$url}/{id}")->whereNumber('id')->group(function () use ($c, $nama) {
            Route::get('/', [$c, 'show'])->name("{$nama}.show");
            Route::get('edit', [$c, 'edit'])->name("{$nama}.edit");
            Route::put('/', [$c, 'update'])->name("{$nama}.update");
            Route::delete('/', [$c, 'destroy'])->name("{$nama}.destroy");
            Route::post('restore', [$c, 'restore'])->name("{$nama}.restore");
            Route::get('log', [$c, 'log'])->name("{$nama}.log");
            // relasi anak
            Route::get('{relasi}/create', [$c, 'createAnak'])->name("{$nama}.anak.create");
            Route::post('{relasi}', [$c, 'storeAnak'])->name("{$nama}.anak.store");
            Route::get('{relasi}/{anak}/edit', [$c, 'editAnak'])->whereNumber('anak')->name("{$nama}.anak.edit");
            Route::put('{relasi}/{anak}', [$c, 'updateAnak'])->whereNumber('anak')->name("{$nama}.anak.update");
            Route::delete('{relasi}/{anak}', [$c, 'destroyAnak'])->whereNumber('anak')->name("{$nama}.anak.destroy");
        });
    }

    // ===== Halaman utama =====

    public function index(Request $request)
    {
        $this->wajib('r');
        $q = trim((string) $request->query('q'));
        $terhapus = $request->boolean('terhapus') && $request->user()->bolehAksi('restore');

        $query = ($this->model)::query()->with($this->with)
            ->when($terhapus, fn ($b) => $b->onlyTrashed())
            ->when($q !== '', function ($b) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $b->where(fn ($w) => collect($this->kolomCari)->each(fn ($k) => $w->orWhere($k, 'like', $like)));
            });

        $nilaiFilter = [];
        foreach ($this->filter as $f) {
            $v = $request->query($f);
            if ($v !== null && $v !== '') {
                $nilaiFilter[$f] = $v;
                $query->where($f, $v);
            }
        }

        return view('master._crud.index', $this->dataView([
            'rows'        => $query->orderBy($this->urutan)->paginate(25)->withQueryString(),
            'q'           => $q,
            'terhapus'    => $terhapus,
            'nilaiFilter' => $nilaiFilter,
        ]));
    }

    public function show(int $id)
    {
        $this->wajib('r');
        $record = $this->cari($id, true);

        $anak = [];
        foreach ($this->relasiAnak as $kunci => $def) {
            if (request()->user()->bolehAkses($def['entitas'], 'r')) {
                $anak[$kunci] = $def + ['rows' => $record->{$kunci}()->get()];
            }
        }

        $tabel = [$this->entitas, ...array_column($this->relasiAnak, 'entitas')];

        return view('master._crud.show', $this->dataView([
            'record'      => $record,
            'anak'        => $anak,
            'dokumen'     => DokumenBukti::untuk($this->entitas, $record->id),
            'terdampak'   => collect($tabel)->flatMap(fn ($t) => DampakCapaian::indikatorTerdampak($t))->unique('id')->values(),
        ]));
    }

    public function create()
    {
        $this->wajib('c');

        return view('master._crud.form', $this->dataView([
            'record' => new ($this->model),
            'action' => route("{$this->routeBase}.store"),
            'method' => 'POST',
            'formFields' => $this->fields,
            'judulForm'  => 'Tambah ' . $this->judul,
            'kembali'    => route($this->routeBase),
        ]));
    }

    public function store(Request $request)
    {
        $this->wajib('c');
        $data = $this->validasi($request, $this->fields);
        $this->cekPeriode($data['periode_id'] ?? null);

        $record = ($this->model)::create($data);

        return redirect()->route("{$this->routeBase}.show", $record->id)->with('sukses', 'Data berhasil disimpan.');
    }

    public function edit(int $id)
    {
        $this->wajib('u');
        $record = $this->cari($id);
        $this->cekPeriode($record->periode_id ?? null);

        return view('master._crud.form', $this->dataView([
            'record' => $record,
            'action' => route("{$this->routeBase}.update", $id),
            'method' => 'PUT',
            'formFields' => $this->fields,
            'judulForm'  => 'Ubah ' . $this->judul,
            'kembali'    => route("{$this->routeBase}.show", $id),
        ]));
    }

    public function update(Request $request, int $id)
    {
        $this->wajib('u');
        $record = $this->cari($id);
        $data = $this->validasi($request, $this->fields, $id);
        $this->cekPeriode($record->periode_id ?? null);
        $this->cekPeriode($data['periode_id'] ?? null);

        $record->update($data);

        return redirect()->route("{$this->routeBase}.show", $id)->with('sukses', 'Perubahan disimpan.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->wajib('d');
        $record = $this->cari($id);
        $this->cekPeriode($record->periode_id ?? null);
        $alasan = $request->validate(['alasan' => ['required', 'string', 'min:5', 'max:500']], [], ['alasan' => 'alasan hapus'])['alasan'];

        ($this->model)::$alasanHapus = $alasan;
        $record->delete(); // soft delete (dev.md §0 prinsip 3)

        return redirect()->route($this->routeBase)->with('sukses', 'Data dihapus. Administrator dapat memulihkannya.');
    }

    public function restore(int $id)
    {
        abort_unless(request()->user()->bolehAksi('restore'), 403);
        ($this->model)::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route("{$this->routeBase}.show", $id)->with('sukses', 'Data dipulihkan.');
    }

    public function log(int $id)
    {
        $this->wajib('r');
        abort_unless(request()->user()->bolehAkses('audit_log', 'r'), 403);
        $record = $this->cari($id, true);

        $entitasAnak = array_column($this->relasiAnak, 'entitas');
        $idAnak = collect($this->relasiAnak)->mapWithKeys(fn ($d, $k) => [$d['entitas'] => $record->{$k}()->withTrashed()->pluck('id')]);

        $log = AuditLog::with('user')
            ->where(function ($w) use ($record, $idAnak) {
                $w->where(fn ($x) => $x->where('entitas', $this->entitas)->where('entitas_id', $record->id));
                foreach ($idAnak as $tabel => $ids) {
                    if ($ids->isNotEmpty()) {
                        $w->orWhere(fn ($x) => $x->where('entitas', $tabel)->whereIn('entitas_id', $ids));
                    }
                }
            })
            ->latest('created_at')->paginate(30);

        return view('master._crud.log', $this->dataView(compact('record', 'log', 'entitasAnak')));
    }

    // ===== Relasi anak =====

    public function createAnak(int $id, string $relasi)
    {
        [$def, $induk] = $this->anak($id, $relasi, 'c');

        return view('master._crud.form', $this->dataView([
            'record'     => new ($def['model']),
            'action'     => route("{$this->routeBase}.anak.store", [$id, $relasi]),
            'method'     => 'POST',
            'formFields' => $def['fields'],
            'judulForm'  => 'Tambah ' . $def['judul'] . ' · ' . $induk->{$this->kolomJudul},
            'kembali'    => route("{$this->routeBase}.show", $id) . '#' . $relasi,
        ]));
    }

    public function storeAnak(Request $request, int $id, string $relasi)
    {
        [$def] = $this->anak($id, $relasi, 'c');
        $data = $this->validasi($request, $def['fields']);
        $this->cekPeriode($data['periode_id'] ?? null);

        $def['model']::create($data + [$def['fk'] => $id]);

        return redirect()->to(route("{$this->routeBase}.show", $id) . '#' . $relasi)->with('sukses', $def['judul'] . ' ditambahkan.');
    }

    public function editAnak(int $id, string $relasi, int $anak)
    {
        [$def, $induk] = $this->anak($id, $relasi, 'u');
        $row = $this->cariAnak($def, $id, $anak);
        $this->cekPeriode($row->periode_id ?? null);

        return view('master._crud.form', $this->dataView([
            'record'     => $row,
            'action'     => route("{$this->routeBase}.anak.update", [$id, $relasi, $anak]),
            'method'     => 'PUT',
            'formFields' => $def['fields'],
            'judulForm'  => 'Ubah ' . $def['judul'] . ' · ' . $induk->{$this->kolomJudul},
            'kembali'    => route("{$this->routeBase}.show", $id) . '#' . $relasi,
        ]));
    }

    public function updateAnak(Request $request, int $id, string $relasi, int $anak)
    {
        [$def] = $this->anak($id, $relasi, 'u');
        $row = $this->cariAnak($def, $id, $anak);
        $data = $this->validasi($request, $def['fields'], $anak);
        $this->cekPeriode($row->periode_id ?? null);
        $this->cekPeriode($data['periode_id'] ?? null);

        $row->update($data);

        return redirect()->to(route("{$this->routeBase}.show", $id) . '#' . $relasi)->with('sukses', 'Perubahan disimpan.');
    }

    public function destroyAnak(Request $request, int $id, string $relasi, int $anak)
    {
        [$def] = $this->anak($id, $relasi, 'd');
        $row = $this->cariAnak($def, $id, $anak);
        $this->cekPeriode($row->periode_id ?? null);
        $alasan = $request->validate(['alasan' => ['required', 'string', 'min:5', 'max:500']], [], ['alasan' => 'alasan hapus'])['alasan'];

        $def['model']::$alasanHapus = $alasan;
        $row->delete();

        return redirect()->to(route("{$this->routeBase}.show", $id) . '#' . $relasi)->with('sukses', $def['judul'] . ' dihapus.');
    }

    // ===== Bantuan =====

    protected function wajib(string $aksi, ?string $entitas = null): void
    {
        abort_unless(request()->user()?->bolehAkses($entitas ?? $this->entitas, $aksi), 403, 'Anda tidak memiliki akses untuk aksi ini.');
    }

    protected function cari(int $id, bool $termasukTerhapus = false): Model
    {
        $q = ($this->model)::query()->with($this->with);
        if ($termasukTerhapus && request()->user()->bolehAksi('restore')) {
            $q->withTrashed();
        }

        return $q->findOrFail($id);
    }

    private function anak(int $id, string $relasi, string $aksi): array
    {
        $def = $this->relasiAnak[$relasi] ?? abort(404);
        $this->wajib($aksi, $def['entitas']);

        return [$def, $this->cari($id)];
    }

    private function cariAnak(array $def, int $id, int $anak): Model
    {
        return $def['model']::where($def['fk'], $id)->findOrFail($anak);
    }

    /** Periode terkunci hanya boleh diubah administrator (dev.md §4.3). */
    protected function cekPeriode(?int $periodeId): void
    {
        if ($periodeId && !request()->user()->hasRole('administrator')
            && Periode::whereKey($periodeId)->where('is_terkunci', true)->exists()) {
            abort(403, 'Periode data ini sudah dikunci. Hubungi Administrator.');
        }
    }

    /** Aturan validasi dari definisi field. */
    protected function validasi(Request $request, array $fields, ?int $id = null): array
    {
        $rules = [];
        $label = [];
        foreach ($fields as $kolom => $f) {
            $r = $f['rules'] ?? 'nullable';
            $r = is_string($r) ? explode('|', str_replace('{id}', (string) ($id ?? 'NULL'), $r)) : $r;

            if (($f['type'] ?? 'text') === 'boolean') {
                $r = ['boolean'];
            } elseif (isset($f['options'])) {
                $r[] = Rule::in(array_map('strval', array_keys($f['options'])));
            } elseif (isset($f['relasi'])) {
                $r[] = Rule::exists((new $f['relasi'][0])->getTable(), 'id')->whereNull('deleted_at');
            }
            $rules[$kolom] = $r;
            $label[$kolom] = $f['label'];
        }

        $data = $request->validate($rules, [], $label);

        foreach ($fields as $kolom => $f) {
            if (($f['type'] ?? 'text') === 'boolean') {
                $data[$kolom] = $request->boolean($kolom);
            } elseif (array_key_exists($kolom, $data) && $data[$kolom] === '') {
                $data[$kolom] = null;
            }
        }

        return $data;
    }

    /** Opsi untuk select relasi */
    public static function opsiRelasi(array $relasi): array
    {
        [$model, $kolom] = $relasi;

        return $model::orderBy($kolom)->pluck($kolom, 'id')->all();
    }

    /** Teks tampilan satu nilai field */
    public static function tampil(Model $row, string $kolom, ?array $f): string
    {
        $v = $row->{$kolom};
        if ($v === null || $v === '') {
            return '–';
        }

        return match (true) {
            ($f['type'] ?? null) === 'boolean' => $v ? 'Ya' : 'Tidak',
            isset($f['options'])                => (string) ($f['options'][$v] ?? $v),
            isset($f['relasi'])                 => (string) ($f['relasi'][0]::withTrashed()->find($v)?->{$f['relasi'][1]} ?? '#' . $v),
            $v instanceof \DateTimeInterface    => $v->format('d/m/Y'),
            default                             => (string) $v,
        };
    }

    private function dataView(array $data): array
    {
        $u = request()->user();

        return $data + [
            'judul'      => $this->judul,
            'routeBase'  => $this->routeBase,
            'entitas'    => $this->entitas,
            'fields'     => $this->fields,
            'kolomTabel' => $this->kolomTabel,
            'kolomJudul' => $this->kolomJudul,
            'filter'     => $this->filter,
            'relasiAnak' => $this->relasiAnak,
            'grup'       => 'Master Data',
            'boleh'      => [
                'c' => $u->bolehAkses($this->entitas, 'c'),
                'u' => $u->bolehAkses($this->entitas, 'u'),
                'd' => $u->bolehAkses($this->entitas, 'd'),
                'restore' => $u->bolehAksi('restore'),
                'log' => $u->bolehAkses('audit_log', 'r'),
            ],
        ];
    }
}
