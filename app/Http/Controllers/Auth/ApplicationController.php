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
use App\Models\Application;
use App\Models\ApplicationImageProof;
use App\Models\ApplicationUrlProof;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\ApplicationControllerTest
 */
class ApplicationController extends Controller
{
    /**
     * Application Add Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('auth.application.create');
    }

    /**
     * Store A New Application.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $application = \resolve(Application::class);
        $application->type = $request->input('type');
        $application->email = $request->input('email');
        $application->referrer = $request->input('referrer');

        if (\config('email-blacklist.enabled') == true) {
            if (\config('captcha.enabled') == false) {
                $v = \validator($request->all(), [
                    'type'     => 'required',
                    'email'    => 'required|string|email|max:70|blacklist|unique:invites|unique:users|unique:applications',
                    'referrer' => 'required',
                    'images.*' => 'filled',
                    'images'   => 'min:2',
                    'links.*'  => 'filled',
                    'links'    => 'min:2',
                ]);
            } else {
                $v = \validator($request->all(), [
                    'type'     => 'required',
                    'email'    => 'required|string|email|max:70|blacklist|unique:invites|unique:users|unique:applications',
                    'referrer' => 'required',
                    'images.*' => 'filled',
                    'images'   => 'min:2',
                    'links.*'  => 'filled',
                    'links'    => 'min:2',
                    'captcha'  => 'hiddencaptcha',
                ]);
            }
        } elseif (\config('captcha.enabled') == false) {
            $v = \validator($request->all(), [
                'type'     => 'required',
                'email'    => 'required|string|email|max:70|unique:invites|unique:users|unique:applications',
                'referrer' => 'required',
                'images.*' => 'filled',
                'images'   => 'min:2',
                'links.*'  => 'filled',
                'links'    => 'min:2',
            ]);
        } else {
            $v = \validator($request->all(), [
                'type'     => 'required',
                'email'    => 'required|string|email|max:70|unique:invites|unique:users|unique:applications',
                'referrer' => 'required',
                'images.*' => 'filled',
                'images'   => 'min:2',
                'links.*'  => 'filled',
                'links'    => 'min:2',
                'captcha'  => 'hiddencaptcha',
            ]);
        }

        if ($v->fails()) {
            return \redirect()->route('application.create')
                ->withErrors($v->errors());
        }
        $application->save();
        // Map And Save IMG Proofs
        $imgs = \collect($request->input('images'))->map(fn ($value) => new ApplicationImageProof(['image' => $value]));
        $application->imageProofs()->saveMany($imgs);
        // Map And Save URL Proofs
        $urls = \collect($request->input('links'))->map(fn ($value) => new ApplicationUrlProof(['url' => $value]));
        $application->urlProofs()->saveMany($urls);

        return \redirect()->route('login')
            ->withSuccess(\trans('auth.application-submitted'));
    }
}
