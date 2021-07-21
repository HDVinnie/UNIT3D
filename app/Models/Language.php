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

namespace App\Models;

use App\Traits\Auditable;

class Language
{
    use Auditable;

    /**
     * Get single flags view.
     *
     * @param string $code
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public static function flag(string $code = 'default'): \Illuminate\Contracts\View\View | \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\Foundation\Application
    {
        if ($code === 'default') {
            $code = \app()->getLocale();
        }

        $name = self::getName($code);
        $code = self::country($code);

        return \view('vendor.language.flag', ['code' => $code, 'name' => $name]);
    }

    /**
     * Get country code based on locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public static function country(string $locale = 'default'): string
    {
        if ($locale === 'default') {
            $locale = \app()->getLocale();
        }

        if (\config('language.mode.code', 'short') === 'short') {
            return \strtolower(\substr(self::getLongCode($locale), 3));
        }

        return \strtolower(\substr($locale, 3));
    }

    /**
     * Get all flags view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public static function flags(): \Illuminate\Contracts\View\View | \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\Foundation\Application
    {
        return \view('vendor.language.flags');
    }

    /**
     * Return true if $code is an allowed lang.
     * Get all allowed languages.
     *
     * @param null $locale
     */
    public static function allowed($locale = null): bool | array
    {
        if ($locale) {
            return \array_key_exists($locale, self::allowed());
        }

        if (\config('language.allowed')) {
            return self::names(\array_merge(\config('language.allowed'), [\config('app.locale')]));
        }

        return self::names([\config('app.locale')]);
    }

    /**
     * Add names to an array of language codes as [$code => $language].
     *
     * @param array $codes
     *
     * @return array
     */
    public static function names(array $codes): array
    {
        // Get mode
        $mode = \config('language.mode');

        // Get languages from config
        $languages = \config('language.all');

        $array = [];

        // Generate an array with $code as key and $code language as value
        foreach ($codes as $code) {
            $langName = 'Unknown';

            foreach ($languages as $language) {
                if ($language[$mode['code']] == $code) {
                    $langName = $language[$mode['name']];
                }
            }

            $array[$code] = $langName;
        }

        return $array;
    }

    /**
     * Add names to an array of language codes as [$language => $code].
     *
     * @param array $langs
     *
     * @return array
     */
    public static function codes(array $langs): array
    {
        // Get mode
        $mode = \config('language.mode');

        // Get languages from config
        $languages = \config('language.all');

        $array = [];

        // Generate an array with $lang as key and $lang code as value
        foreach ($langs as $lang) {
            $langCode = 'unk';

            foreach ($languages as $language) {
                if ($language[$mode['name']] == $lang) {
                    $langCode = $language[$mode['code']];
                }
            }

            $array[$lang] = $langCode;
        }

        return $array;
    }

    /**
     * Returns the url to set up language and return back.
     *
     * @param string $code
     *
     * @return string
     */
    public static function back(string $code): string
    {
        return \route('back', ['locale' => $code]);
    }

    /**
     * Returns the url to set up language and return to url('/').
     *
     * @param string $code
     *
     * @return string
     */
    public static function home(string $code): string
    {
        return \route('home', ['locale' => $code]);
    }

    /**
     * Returns the language code.
     *
     * @param string $name
     *
     * @return string
     */
    public static function getCode(string $name = 'default'): string
    {
        if ($name === 'default') {
            $name = self::getName();
        }

        return self::codes([$name])[$name];
    }

    /**
     * Returns the language long code.
     *
     * @param string $short
     *
     * @return string
     */
    public static function getLongCode(string $short = 'default'): string
    {
        if ($short === 'default') {
            $short = \app()->getLocale();
        }

        $long = 'en-GB';

        // Get languages from config
        foreach (\config('language.all') as $language) {
            if ($language['short'] != $short) {
                continue;
            }

            $long = $language['long'];
        }

        return $long;
    }

    /**
     * Returns the language short code.
     *
     * @param string $long
     *
     * @return string
     */
    public static function getShortCode(string $long = 'default'): string
    {
        if ($long === 'default') {
            $long = \app()->getLocale();
        }

        $short = 'en';

        // Get languages from config
        $languages = \config('language.all');

        foreach ($languages as $language) {
            if ($language['long'] != $long) {
                continue;
            }

            $short = $language['short'];
        }

        return $short;
    }

    /**
     * Returns the language name.
     *
     * @param string $code
     *
     * @return string
     */
    public static function getName(string $code = 'default'): string
    {
        if ($code === 'default') {
            $code = \app()->getLocale();
        }

        return self::names([$code])[$code];
    }
}
