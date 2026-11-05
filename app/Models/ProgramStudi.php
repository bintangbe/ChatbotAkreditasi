<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramStudi extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'program_studi';

    protected $fillable = ['kode', 'nama', 'jenjang', 'fakultas', 'kampus', 'kelas_internasional', 'pelaksana_mbkm'];

    protected function casts(): array
    {
        return [
            'kelas_internasional' => 'boolean',
            'pelaksana_mbkm'      => 'boolean',
        ];
    }
}
