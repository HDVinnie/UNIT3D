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

class Collection
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
                    'append_to_response' => 'videos,images,credits',
                ],
            ]
        );

        $response = $this->client->request('get', 'https://api.TheMovieDB.org/3/collection/'.$id);

        $this->data = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getData()
    {
        return $this->data;
    }

    public function get_name(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['name']);
    }

    public function get_overview(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['overview']);
    }

    public function get_id()
    {
        return $this->data['id'];
    }

    public function get_backdrop(): ?string
    {
        if ($this->data['backdrop_path']) {
            return 'https://image.tmdb.org/t/p/original'.$this->data['backdrop_path'];
        }

        return null;
    }

    public function get_poster(): ?string
    {
        if ($this->data['poster_path']) {
            return 'https://image.tmdb.org/t/p/original'.$this->data['poster_path'];
        }

        return null;
    }
}
