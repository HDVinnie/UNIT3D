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

namespace App\Http\Middleware;

use App\Traits\TwoStep;
use Closure;
use Illuminate\Http\Request;

class TwoStepAuth
{
    use TwoStep;

    /**
     * Handle an incoming request.
     *
     *
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);
        $uri = $request->path();
        $nextUri = \config('app.url').'/'.$uri;
        $user = $request->user();

        switch ($uri) {
            case 'twostep/needed':
            case 'password/reset':
            case 'register':
            case 'logout':
            case 'login':
                break;

            default:
                \session(['nextUri' => $nextUri]);

                if (\config('auth.TwoStepEnabled') && $user->twostep == 1 && ! $this->twoStepVerification()) {
                    return \redirect()->route('verificationNeeded');
                }

                break;
        }

        return $response;
    }
}
