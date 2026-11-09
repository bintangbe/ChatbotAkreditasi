<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian di route: ->middleware('aksi:setujui_capaian') */
class CekAksi
{
    public function handle(Request $request, Closure $next, string $aksi): Response
    {
        abort_unless($request->user()?->bolehAksi($aksi), 403, 'Anda tidak memiliki wewenang untuk aksi ini.');

        return $next($request);
    }
}
