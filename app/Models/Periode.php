<?php

namespace App\Models;

use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use Blameable, SoftDeletes;

    protected $table = 'periode';

    protected $fillable = ['kode', 'nama', 'jenis', 'tanggal_mulai', 'tanggal_akhir', 'is_aktif', 'is_terkunci'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_akhir' => 'date',
            'is_aktif'      => 'boolean',
            'is_terkunci'   => 'boolean',
        ];
    }

    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->orderByDesc('tanggal_mulai')->first();
    }
}
