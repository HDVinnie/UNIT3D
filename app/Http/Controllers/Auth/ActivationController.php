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

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\UserActivation;

/**
 * @see \Tests\Feature\Http\Controllers\Auth\ActivationControllerTest
 */
class ActivationController extends Controller
{
    public function activate($token): \Illuminate\Http\RedirectResponse
    {
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $memberGroup = \cache()->rememberForever('member_group', fn () => Group::where('slug', '=', 'user')->pluck('id'));

        $activation = UserActivation::with('user')->where('token', '=', $token)->firstOrFail();
        if ($activation->user->id && $activation->user->group->id != $bannedGroup[0]) {
            $activation->user->active = 1;
            $activation->user->can_upload = 1;
            $activation->user->can_download = 1;
            $activation->user->can_request = 1;
            $activation->user->can_comment = 1;
            $activation->user->can_invite = 1;
            $activation->user->group_id = $memberGroup[0];
            $activation->user->save();

            $activation->delete();

            return \redirect()->route('login')
                ->withSuccess(\trans('auth.activation-success'));
        }

        return \redirect()->route('login')
            ->withErrors(\trans('auth.activation-error'));
    }
}
