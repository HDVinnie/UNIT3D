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

class Season
{
    /**
     * @var \GuzzleHttp\Client|mixed
     */
    public mixed $client;
    public const API_BASE_URI = 'https://api.TheMovieDB.org/3';
    public mixed $data;

    public function __construct($id, $season)
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
                    'append_to_response' => 'videos,images,credits,external_ids',
                ],
            ]
        );

        $response = $this->client->request('get', 'https://api.TheMovieDB.org/3/tv/'.$id.'/season/'.$season);

        $this->data = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getData()
    {
        return $this->data;
    }

    public function _id()
    {
        return $this->data['_id'];
    }

    public function get_air_date()
    {
        return $this->data['air_date'];
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

    public function get_poster(): string
    {
        return 'https://image.tmdb.org/t/p/original'.$this->data['poster_path'];
    }

    public function get_season_number(): string
    {
        return \sprintf('%02d', $this->data['seasons']);
    }

    public function get_status()
    {
        return $this->data['status'];
    }
}
