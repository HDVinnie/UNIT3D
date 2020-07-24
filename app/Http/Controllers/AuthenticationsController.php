<?php
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

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthenticationsController extends Controller
{
    /**
     * Show A Users Authentications.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $username)
    {
        $user = User::with(['authentications', 'devices'])->where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);

        return view('user.authentications', ['user' => $user]);
    }
}
