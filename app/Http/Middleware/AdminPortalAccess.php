<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin portalına erişimde ek yetki katmanı.
 * Sadece takimhane_sor veya super_admin rolündeki kullanıcılara izin verir.
 */
class AdminPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isTakimhaneSorumlusu()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Yetkisiz erişim.'], 403);
            }
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
