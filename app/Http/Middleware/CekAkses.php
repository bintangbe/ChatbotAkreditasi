<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route: ->middleware('akses:pegawai,r')
 * Menolak (403) kalau peran user tidak punya aksi tsb. pada entitas (config/permissions.php).
 */
class CekAkses
{
    public function handle(Request $request, Closure $next, string $entitas, string $aksi = 'r'): Response
    {
        $user = $request->user();

        abort_unless($user && $user->bolehAkses($entitas, $aksi), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
