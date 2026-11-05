<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verifikasi extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'verifikasi';

    protected $fillable = ['capaian_id', 'user_id', 'status', 'catatan'];

    public function capaian(): BelongsTo
    {
        return $this->belongsTo(CapaianIndikator::class, 'capaian_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
