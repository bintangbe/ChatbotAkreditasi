<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

// Mengisi created_by / updated_by otomatis (dev.md §3.1)
trait Blameable
{
    public static function bootBlameable(): void
    {
        static::creating(function ($model) {
            if ($id = Auth::id()) {
                $model->created_by ??= $id;
                $model->updated_by = $id;
            }
        });

        static::updating(function ($model) {
            if ($id = Auth::id()) {
                $model->updated_by = $id;
            }
        });
    }
}
