<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route: ->middleware('peran:kaprodi,tim_gkm')
 * Hanya pengguna dengan salah satu peran tsb. yang boleh masuk (dev.md §6).
 */
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        abort_unless($request->user()?->hasRole(...$peran), 403, 'Halaman ini tidak tersedia untuk peran Anda.');

        return $next($request);
    }
}
