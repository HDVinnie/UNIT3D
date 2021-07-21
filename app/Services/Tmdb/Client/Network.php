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

namespace App\Services\Tmdb\Client;

class Network
{
    /**
     * @var \GuzzleHttp\Client|mixed
     */
    public mixed $client;
    public const API_BASE_URI = 'https://api.TheMovieDB.org/3';
    public mixed $data;

    public function __construct($id)
    {
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri'    => self::API_BASE_URI,
                'verify'      => false,
                'http_errors' => false,
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'query' => [
                    'api_key'            => \config('api-keys.tmdb'),
                    'language'           => \config('app.locale'),
                    'append_to_response' => 'images',
                ],
            ]
        );

        $response = $this->client->request('get', 'https://api.TheMovieDB.org/3/network/'.$id);

        $this->data = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getData()
    {
        return $this->data;
    }

    public function get_birthday()
    {
        return $this->data['birthday'];
    }

    public function get_known_for_department(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['known_for_department']);
    }

    public function get_deathday(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['deathday']);
    }

    public function get_id()
    {
        return $this->data['id'];
    }

    public function get_foto(): string
    {
        return 'https://image.tmdb.org/t/p/original'.$this->data['profile_path'];
    }

    public function get_name(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['name']);
    }

    public function get_gender()
    {
        return $this->data['gender'];
    }

    public function get_biography()
    {
        return $this->data['biography'];
    }

    public function get_popularity()
    {
        return $this->data['popularity'];
    }

    public function get_place_of_birth()
    {
        return $this->data['place_of_birth'];
    }

    public function get_adult()
    {
        return $this->data['adult'];
    }

    public function get_imdb_id()
    {
        return $this->data['imdb_id'];
    }

    public function get_homepage()
    {
        return $this->data['homepage'];
    }
}
