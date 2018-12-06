<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Staff;

use App\Invite;
use App\Http\Controllers\Controller;

class InviteController extends Controller
{
    /**
     * Invites Log.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getInvites()
    {
        $invites = Invite::latest()->paginate(25);
        $invitecount = Invite::count();

        return view('Staff.invites.index', ['invites' => $invites, 'invitecount' => $invitecount]);
    }
}
