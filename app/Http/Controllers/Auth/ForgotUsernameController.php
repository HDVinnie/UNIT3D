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
use App\Models\User;
use App\Notifications\UsernameReminder;
use Illuminate\Http\Request;

/**
 * @see \Tests\Feature\Http\Controllers\Auth\ForgotUsernameControllerTest
 */
class ForgotUsernameController extends Controller
{
    /**
     * Forgot Username Form.
     */
    public function showForgotUsernameForm(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('auth.username');
    }

    /**
     * Send Username Reminder.
     */
    public function sendUsernameReminder(Request $request): \Illuminate\Http\RedirectResponse
    {
        $email = $request->get('email');

        if (\config('captcha.enabled') == false) {
            $v = \validator($request->all(), [
                'email' => 'required',
            ]);
        } else {
            $v = \validator($request->all(), [
                'email'   => 'required',
                'captcha' => 'hiddencaptcha',
            ]);
        }

        if ($v->fails()) {
            return \redirect()->route('username.request')
                ->withErrors($v->errors());
        }
        $user = User::where('email', '=', $email)->first();
        if (empty($user)) {
            return \redirect()->route('username.request')
                ->withErrors(\trans('email.no-email-found'));
        }
        //send username reminder notification
        $user->notify(new UsernameReminder());

        return \redirect()->route('login')
            ->withSuccess(\trans('email.username-sent'));
    }
}
