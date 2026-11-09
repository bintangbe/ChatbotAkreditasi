<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenRelasi extends Model
{
    public $timestamps = false;

    protected $table = 'dokumen_relasi';

    protected $fillable = ['dokumen_id', 'entitas', 'entitas_id', 'periode_id'];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(DokumenBukti::class, 'dokumen_id');
    }
}
