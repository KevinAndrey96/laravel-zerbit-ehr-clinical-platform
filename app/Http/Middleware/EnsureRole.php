<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route group to staff with one of the given roles (any role
 * when none is given) and signs out accounts that were deactivated while
 * their session was still open.
 */
final class EnsureRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->guest(route('login'));
        }
        if (! $user->active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta está desactivada. Comunícate con la coordinación de la clínica.']);
        }
        abort_unless($roles === [] || in_array($user->role, array_map(Role::from(...), $roles), true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
