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

namespace App\Services\Tmdb;

class TMDB
{
    public function image($type, $array): ?string
    {
        if (isset($array[$type.'_path'])) {
            return 'https://image.tmdb.org/t/p/original'.$array[$type.'_path'];
        }

        return null;
    }

    public function trailer($array): ?string
    {
        if (isset($array['videos']['results'])) {
            return 'https://www.youtube.com/embed/'.$array['videos']['results'][0]['key'];
        }

        return null;
    }

    public function ifHasItems($type, $array)
    {
        return $array[$type][0] ?? null;
    }

    public function ifExists($type, $array)
    {
        if (isset($array[$type]) && ! empty($array[$type])) {
            return $array[$type];
        }

        return null;
    }

    public function cast_array($cast): array
    {
        return [
            'character' => $cast['character'],
            'credit_id' => $cast['credit_id'],
            'gender'    => $cast['gender'],
            'name'      => $cast['name'],
            'order'     => $cast['order'],
            'still'     => $this->image('profile', $cast),
        ];
    }

    public function person_array($person): array
    {
        return [
            'birthday'             => $this->ifExists('birthday', $person),
            'known_for_department' => $this->ifExists('known_for_department', $person),
            'deathday'             => $this->ifExists('deathday', $person),
            'name'                 => $this->ifExists('name', $person),
            //"also_known_as" => $person['also_known_as'] ?? null,
            'gender'         => $this->ifExists('gender', $person),
            'biography'      => $this->ifExists('biography', $person),
            'popularity'     => $this->ifExists('popularity', $person),
            'place_of_birth' => $this->ifExists('place_of_birth', $person),
            'still'          => $this->image('profile', $person),
            'adult'          => $this->ifExists('adult', $person),
            'imdb_id'        => $this->ifExists('imdb_id', $person),
            'homepage'       => $this->ifExists('homepage', $person),
        ];
    }
}
