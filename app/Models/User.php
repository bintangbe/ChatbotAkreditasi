<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // `name` dipakai sebagai nama lengkap (dev.md §3.2: nama_lengkap)
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'pegawai_id',
        'prodi_id',
        'is_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_aktif' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    /** Kode peran milik user, di-cache per request. */
    public function roleCodes(): array
    {
        return $this->roleCodesCache ??= $this->roles()->pluck('kode')->all();
    }

    private ?array $roleCodesCache = null;

    public function hasRole(string ...$kode): bool
    {
        return (bool) array_intersect($kode, $this->roleCodes());
    }

    /**
     * Cek hak akses dari matriks config/permissions.php.
     * $aksi: c (create), r (read), u (update), d (delete).
     */
    public function bolehAkses(string $entitas, string $aksi = 'r'): bool
    {
        $matriks = config('permissions.roles', []);
        $umum = config('permissions.semua_pengguna', []);

        foreach ([$umum, ...array_map(fn ($r) => $matriks[$r] ?? [], $this->roleCodes())] as $hak) {
            $izin = $hak[$entitas] ?? $hak['*'] ?? '';
            if (str_contains($izin, $aksi)) {
                return true;
            }
        }

        return false;
    }
}
