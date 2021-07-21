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
if (! function_exists('appurl')) {
    function appurl()
    {
        return config('app.url');
    }
}

if (! function_exists('href_profile')) {
    function href_profile($user): string
    {
        $appurl = appurl();

        return sprintf('%s/users/%s', $appurl, $user->username);
    }
}

if (! function_exists('href_article')) {
    function href_article($article): string
    {
        $appurl = appurl();

        return sprintf('%s/articles/%s', $appurl, $article->id);
    }
}

if (! function_exists('href_torrent')) {
    function href_torrent($torrent): string
    {
        $appurl = appurl();

        return sprintf('%s/torrents/%s', $appurl, $torrent->id);
    }
}

if (! function_exists('href_request')) {
    function href_request($torrentRequest): string
    {
        $appurl = appurl();

        return sprintf('%s/requests/%s', $appurl, $torrentRequest->id);
    }
}

if (! function_exists('href_poll')) {
    function href_poll($poll): string
    {
        $appurl = appurl();

        return sprintf('%s/polls/%s', $appurl, $poll->id);
    }
}

if (! function_exists('href_playlist')) {
    function href_playlist($playlist): string
    {
        $appurl = appurl();

        return sprintf('%s/playlists/%s', $appurl, $playlist->id);
    }
}

if (! function_exists('href_collection')) {
    function href_collection($collection): string
    {
        $appurl = appurl();

        return sprintf('%s/mediahub/collections/%s', $appurl, $collection->id);
    }
}

if (! function_exists('tmdb_image')) {
    function tmdb_image($type, $original): array | string
    {
        $new = match ($type) {
            'back_big'     => 'w1280',
            'back_small'   => 'w780',
            'poster_big'   => 'w500',
            'poster_mid'   => 'w342',
            'poster_small' => 'w92',
            'cast_face'    => 'w138_and_h175_face',
            'cast_mid'     => 'w185',
            'cast_big', 'logo_mid' => 'w300',
            'still_mid'    => 'w400',
            'logo_small'   => 'h60',
            default        => 'original',
        };

        return \str_replace('/original/', '/'.$new.'/', $original);
    }
}

if (! function_exists('modal_style')) {
    function modal_style(): string
    {
        return (auth()->user()->style == 0) ? '' : ' modal-dark';
    }
}

if (! function_exists('rating_color')) {
    function rating_color($number): string
    {
        if ($number > 0 && $number <= 3.9) {
            return 'text-danger';
        }

        if ($number >= 4 && $number <= 6.9) {
            return 'text-warning';
        }

        if ($number >= 7 && $number <= 10) {
            return 'text-success';
        }
    }
}

if (! function_exists('languageFlag')) {
    function language_flag($language): ?string
    {
        $flag = match ($language) {
            'English'    => 'us',
            'Arabic'     => 'ae',
            'Belarusian' => 'by',
            'Bulgarian'  => 'bg',
            'Catalan'    => 'ad',
            'Chinese'    => 'cn',
            'Croatian'   => 'hr',
            'Czech'      => 'cz',
            'Danish'     => 'dk',
            'Dutch'      => 'nl',
            'Estonian'   => 'ee',
            'Finnish'    => 'fi',
            'French'     => 'fr',
            'Georgian'   => 'ge',
            'German'     => 'de',
            'Greek'      => 'gr',
            'Hebrew'     => 'il',
            'Hindi', 'Tamil', 'Telugu' => 'in',
            'Hungarian'  => 'hu',
            'Icelandic'  => 'is',
            'Indonesian' => 'id',
            'Italian'    => 'it',
            'Japanese'   => 'jp',
            'Korean'     => 'kr',
            'Latvian'    => 'lv',
            'Lithuanian' => 'lt',
            'Malay'      => 'my',
            'Norwegian', 'Norwegian Bokmal' => 'no',
            'Persian'    => 'ir',
            'Polish'     => 'pl',
            'Portuguese' => 'pt',
            'Romanian'   => 'ro',
            'Russian'    => 'ru',
            'Serbian'    => 'rs',
            'Slovak'     => 'sk',
            'Slovenian'  => 'si',
            'Spanish'    => 'es',
            'Swedish'    => 'se',
            'Tagalog'    => 'ph',
            'Thai'       => 'th',
            'Turkish'    => 'tr',
            'Ukrainian'  => 'ua',
            'Vietnamese' => 'vn',
            default      => null,
        };
        if ($flag !== null) {
            $flagUrl = '/img/flags/'.$flag.'.png';
        } else {
            $flagUrl = null;
        }

        return $flagUrl;
    }
}
