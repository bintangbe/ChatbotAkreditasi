<?php

namespace App\Models;

use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetIndikator extends Model
{
    use Blameable, SoftDeletes;

    protected $table = 'target_indikator';

    protected $fillable = ['indikator_id', 'periode_id', 'prodi_id', 'nilai_target', 'nilai_baseline', 'catatan'];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }
}
