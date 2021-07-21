<?php

declare(strict_types=1);
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // Default Laravel
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //\App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,

        // Extra
        \Bepsvpt\SecureHeaders\SecureHeadersMiddleware::class,
        \App\Http\Middleware\Http2ServerPush::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\UpdateLastAction::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin'         => \App\Http\Middleware\CheckForAdmin::class,
        'auth'          => \App\Http\Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'banned'        => \App\Http\Middleware\CheckIfBanned::class,
        'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'csrf'          => \App\Http\Middleware\VerifyCsrfToken::class,
        'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'language'      => \App\Http\Middleware\SetLanguage::class,
        'modo'          => \App\Http\Middleware\CheckForModo::class,
        'owner'         => \App\Http\Middleware\CheckForOwner::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'twostep'       => \App\Http\Middleware\TwoStepAuth::class,
        'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
