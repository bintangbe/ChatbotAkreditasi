<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/** Akses registri config('siakred.entitas'): label, model, dan tautan halaman per tabel. */
class Entitas
{
    public static function def(string $tabel): ?array
    {
        return config("siakred.entitas.{$tabel}");
    }

    public static function label(string $tabel): string
    {
        return self::def($tabel)['label'] ?? str_replace('_', ' ', $tabel);
    }

    public static function cari(string $tabel, int $id): ?Model
    {
        $model = self::def($tabel)['model'] ?? null;
        if (!$model) {
            return null;
        }
        $q = $model::query();

        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)
            ? $q->withTrashed()->find($id) : $q->find($id);
    }

    /** URL halaman detail baris ini (untuk anak: halaman induknya), atau null. */
    public static function url(string $tabel, int $id): ?string
    {
        $def = self::def($tabel);
        if (!$def) {
            return null;
        }
        if (isset($def['route'])) {
            return route($def['route'] . '.show', $id);
        }
        if (isset($def['induk'])) {
            [$tabelInduk, $fk] = $def['induk'];
            $indukId = self::cari($tabel, $id)?->{$fk};

            return $indukId ? self::url($tabelInduk, $indukId) : null;
        }

        return null;
    }
}
