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
use App\Jobs\SendActivationMail;
use App\Models\Group;
use App\Models\Invite;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Models\UserActivation;
use App\Models\UserNotification;
use App\Models\UserPrivacy;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * RegisterController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Registration Form.
     *
     * @param null $code
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function registrationForm($code = null): \Illuminate\Contracts\View\Factory | \Illuminate\View\View | \Illuminate\Http\RedirectResponse
    {
        // Make sure open reg is off, invite code is not present and application signups enabled
        if ($code === 'null' && \config('other.invite-only') == 1 && \config('other.application_signups') == true) {
            return \redirect()->route('application.create')
                ->withInfo(\trans('auth.allow-invite-appl'));
        }

        // Make sure open reg is off and invite code is not present
        if ($code === 'null' && \config('other.invite-only') == 1) {
            return \redirect()->route('login')
                ->withWarning(\trans('auth.allow-invite'));
        }

        return \view('auth.register', ['code' => $code]);
    }

    public function register(Request $request, $code = null): \Illuminate\Http\RedirectResponse
    {
        // Make sure open reg is off and invite code exist and has not been used already
        $key = Invite::where('code', '=', $code)->first();
        if (\config('other.invite-only') == 1 && (! $key || $key->accepted_by !== null)) {
            return \redirect()->route('registrationForm', ['code' => $code])
                ->withErrors(\trans('auth.invalid-key'));
        }

        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));

        $user = new User();
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->passkey = \md5(\uniqid('', true).\time().\microtime());
        $user->rsskey = \md5(\uniqid('', true).\time().\microtime().$user->password);
        $user->uploaded = \config('other.default_upload');
        $user->downloaded = \config('other.default_download');
        $user->style = \config('other.default_style', 0);
        $user->locale = \config('app.locale');
        $user->group_id = $validatingGroup[0];

        if (\config('email-blacklist.enabled') == true) {
            if (\config('captcha.enabled') == false) {
                $v = \validator($request->all(), [
                    'username' => 'required|alpha_dash|string|between:3,25|unique:users',
                    'password' => 'required|string|between:8,16',
                    'email'    => 'required|string|email|max:70|blacklist|unique:users',
                ]);
            } else {
                $v = \validator($request->all(), [
                    'username' => 'required|alpha_dash|string|between:3,25|unique:users',
                    'password' => 'required|string|between:8,16',
                    'email'    => 'required|string|email|max:70|blacklist|unique:users',
                    'captcha'  => 'hiddencaptcha',
                ]);
            }
        } elseif (\config('captcha.enabled') == false) {
            $v = \validator($request->all(), [
                'username' => 'required|alpha_dash|string|between:3,25|unique:users',
                'password' => 'required|string|between:8,16',
                'email'    => 'required|string|email|max:70|unique:users',
            ]);
        } else {
            $v = \validator($request->all(), [
                'username' => 'required|alpha_dash|string|between:3,25|unique:users',
                'password' => 'required|string|between:6,16',
                'email'    => 'required|string|email|max:70|unique:users',
                'captcha'  => 'hiddencaptcha',
            ]);
        }

        if ($v->fails()) {
            return \redirect()->route('registrationForm', ['code' => $code])
                ->withErrors($v->errors());
        }
        $user->save();
        $userPrivacy = new UserPrivacy();
        $userPrivacy->setDefaultValues();
        $userPrivacy->user_id = $user->id;
        $userPrivacy->save();
        $userNotification = new UserNotification();
        $userNotification->setDefaultValues();
        $userNotification->user_id = $user->id;
        $userNotification->save();
        if ($key) {
            // Update The Invite Record
            $key->accepted_by = $user->id;
            $key->accepted_at = new Carbon();
            $key->save();
        }
        // Handle The Activation System
        $token = \hash_hmac('sha256', $user->username.$user->email.Str::random(16), \config('app.key'));
        $userActivation = new UserActivation();
        $userActivation->user_id = $user->id;
        $userActivation->token = $token;
        $userActivation->save();
        $this->dispatch(new SendActivationMail($user, $token));
        // Select A Random Welcome Message
        $profileUrl = \href_profile($user);
        $welcomeArray = [
            \sprintf('[url=%s]%s[/url], Welcome to ', $profileUrl, $user->username).\config('other.title').'! Hope you enjoy the community :rocket:',
            \sprintf("[url=%s]%s[/url], We've been expecting you :space_invader:", $profileUrl, $user->username),
            \sprintf("[url=%s]%s[/url] has arrived. Party's over. :cry:", $profileUrl, $user->username),
            \sprintf("It's a bird! It's a plane! Nevermind, it's just [url=%s]%s[/url].", $profileUrl, $user->username),
            \sprintf('Ready player [url=%s]%s[/url].', $profileUrl, $user->username),
            \sprintf('A wild [url=%s]%s[/url] appeared.', $profileUrl, $user->username),
            'Welcome to '.\config('other.title').\sprintf(' [url=%s]%s[/url]. We were expecting you ( ͡° ͜ʖ ͡°)', $profileUrl, $user->username),
        ];
        $selected = \mt_rand(0, \count($welcomeArray) - 1);
        $this->chatRepository->systemMessage(
            $welcomeArray[$selected]
        );
        // Send Welcome PM
        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = 1;
        $privateMessage->receiver_id = $user->id;
        $privateMessage->subject = \config('welcomepm.subject');
        $privateMessage->message = \config('welcomepm.message');
        $privateMessage->save();

        return \redirect()->route('login')
            ->withSuccess(\trans('auth.register-thanks'));
    }
}
