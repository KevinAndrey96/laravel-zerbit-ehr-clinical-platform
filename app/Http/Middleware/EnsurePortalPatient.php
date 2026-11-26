<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Patient portal session: opened by a signed email link and closed after
 * thirty minutes without activity.
 */
final class EnsurePortalPatient
{
    public const string SESSION_KEY = 'portal.patient_id';

    public const string SEEN_KEY = 'portal.seen_at';

    public const int IDLE_MINUTES = 30;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $id = $session->get(self::SESSION_KEY);
        $seen = $session->get(self::SEEN_KEY);
        $now = CarbonImmutable::now()->getTimestamp();
        $patient = is_int($id) ? Patient::query()->find($id) : null;

        if ($patient === null || ! is_int($seen) || $now - $seen > self::IDLE_MINUTES * 60) {
            $session->forget([self::SESSION_KEY, self::SEEN_KEY]);

            return redirect()->route('portal.login')->withErrors(['email' => 'Tu sesión en el portal terminó. Pide un enlace nuevo.']);
        }

        $session->put(self::SEEN_KEY, $now);
        $request->attributes->set('patient', $patient);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
