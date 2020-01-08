<?php

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class UpdateLastAction
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$user = $request->user()) {
            return $next($request);
        }

        $user->last_action = now();
        $user->save();

        return $next($request);
    }
}
