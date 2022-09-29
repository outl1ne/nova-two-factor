<?php

namespace Outl1ne\NovaTwoFactor\Http\Middleware;

use Closure;
use Outl1ne\NovaTwoFactor\NovaTwoFactor;
use Outl1ne\NovaTwoFactor\TwoFaAuthenticator;

class TwoFa
{
    private $novaGuard;

    public function __construct()
    {
        $this->novaGuard = config('nova.guard', 'web');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function handle($request, Closure $next)
    {
        $excludedRoutes = NovaTwoFactor::getExcludedRoutes();

        if (!config('nova-two-factor.enabled') || in_array($request->path(), $excludedRoutes)) {
            return $next($request);
        }

        $authenticator = app(TwoFaAuthenticator::class)->boot($request);

        if (auth($this->novaGuard)->guest() || $authenticator->isAuthenticated()) {
            return $next($request);
        }

        $twoFaState = auth($this->novaGuard)->user()->twoFa ?? null;

        // turn off security if no user2fa record
        if (!$twoFaState || !$twoFaState->enabled) {
            return $next($request);
        }

        return response(view('nova-two-factor::sign-in'));
    }
}
