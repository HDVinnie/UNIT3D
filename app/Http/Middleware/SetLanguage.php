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

use App\Models\Language;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * This function checks if language to set is an allowed lang of config.
     *
     * @param string $locale
     */
    private function setLocale(string $locale): void
    {
        // Check if is allowed and set default locale if not
        if (! Language::allowed($locale)) {
            $locale = \config('app.locale');
        }

        // Set app language
        App::setLocale($locale);

        // Set carbon language
        if (\config('language.carbon')) {
            // Carbon uses only language code
            if (\config('language.mode.code') === 'long') {
                $locale = \explode('-', $locale)[0];
            }

            Carbon::setLocale($locale);
        }

        // Set date language
        if (\config('language.date')) {
            // Date uses only language code
            if (\config('language.mode.code') === 'long') {
                $locale = \explode('-', $locale)[0];
            }

            \Date::setLocale($locale);
        }
    }

    public function setDefaultLocale(): void
    {
        $this->setLocale(\config('app.locale'));
    }

    public function setUserLocale(): void
    {
        $user = \auth()->user();

        if ($user->locale) {
            $this->setLocale($user->locale);
        } else {
            $this->setDefaultLocale();
        }
    }

    public function setSystemLocale($request): void
    {
        if ($request->session()->has('locale')) {
            $this->setLocale(\session('locale'));
        } else {
            $this->setDefaultLocale();
        }
    }

    /**
     * Handle an incoming request.
     */
    public function handle(\Illuminate\Http\Request $request, Closure $next): mixed
    {
        if ($request->has('lang')) {
            $this->setLocale($request->get('lang'));
        } elseif (\auth()->check()) {
            $this->setUserLocale();
        } else {
            $this->setSystemLocale($request);
        }

        return $next($request);
    }
}
