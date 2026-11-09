<?php

namespace App\Http\Controllers\Master;

use App\Models\ProgramStudi;

class ProgramStudiController extends BaseCrudController
{
    protected string $model = ProgramStudi::class;
    protected string $entitas = 'program_studi';
    protected string $judul = 'Program Studi';
    protected string $routeBase = 'master.prodi';
    protected string $urutan = 'kode';
    protected array $kolomTabel = ['kode', 'jenjang', 'nama', 'kampus', 'kelas_internasional', 'pelaksana_mbkm'];
    protected array $kolomCari = ['kode', 'nama', 'fakultas'];
    protected array $filter = ['jenjang'];

    protected array $fields = [
        'kode'     => ['label' => 'Kode', 'type' => 'text', 'rules' => 'required|string|max:20|unique:program_studi,kode,{id}'],
        'nama'     => ['label' => 'Nama program studi', 'type' => 'text', 'rules' => 'required|string|max:150'],
        'jenjang'  => ['label' => 'Jenjang', 'type' => 'select', 'rules' => 'required',
                       'options' => ['D3' => 'D3', 'D4' => 'D4', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3', 'Profesi' => 'Profesi']],
        'fakultas' => ['label' => 'Fakultas', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
        'kampus'   => ['label' => 'Kampus', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
        'kelas_internasional' => ['label' => 'Menyelenggarakan kelas internasional', 'type' => 'boolean', 'bantuan' => 'Indikator 1.7 no.7'],
        'pelaksana_mbkm'      => ['label' => 'Melaksanakan program Merdeka Belajar', 'type' => 'boolean', 'bantuan' => 'Indikator 1.7 no.8'],
    ];
}
