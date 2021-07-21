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

class Movie
{
    /**
     * @var \GuzzleHttp\Client|mixed
     */
    public mixed $client;
    public const API_BASE_URI = 'https://api.themoviedb.org/3/';
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
                    'append_to_response' => 'videos,images,credits,external_ids,keywords,recommendations',
                ],
            ]
        );

        $response = $this->client->request('get', 'https://api.themoviedb.org/3/movie/'.$id);

        $this->data = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getData()
    {
        return $this->data;
    }

    public function get_background(): ?string
    {
        if (isset($this->data['backdrop_path'])) {
            return 'https://image.tmdb.org/t/p/original'.$this->data['backdrop_path'];
        }

        return null;
    }

    public function get_adult()
    {
        return $this->data['adult'];
    }

    public function get_belongs_to_collection()
    {
        return $this->data['belongs_to_collection'];
    }

    public function get_budget(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['budget']);
    }

    public function get_genres()
    {
        return $this->data['genres'];
    }

    public function get_homepage(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['homepage']);
    }

    public function get_id()
    {
        return $this->data['id'];
    }

    public function get_imdb_id(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['imdb_id']);
    }

    public function get_original_title(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['original_title']);
    }

    public function get_overview(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['overview']);
    }

    public function get_popularity()
    {
        return $this->data['popularity'];
    }

    public function get_poster(): ?string
    {
        if (isset($this->data['poster_path'])) {
            return 'https://image.tmdb.org/t/p/original'.$this->data['poster_path'];
        }

        return null;
    }

    public function get_production_companies()
    {
        return $this->data['production_companies'];
    }

    public function get_production_countries()
    {
        return $this->data['production_countries'];
    }

    public function get_release_date()
    {
        return $this->data['release_date'] ?? null;
    }

    public function get_revenue()
    {
        return $this->data['revenue'];
    }

    public function get_runtime()
    {
        return $this->data['runtime'];
    }

    public function get_status()
    {
        return $this->data['status'];
    }

    public function get_tagline(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['tagline']);
    }

    public function get_title(): array | string | null
    {
        return \preg_replace('/[[:^print:]]/', '', $this->data['title']);
    }

    public function get_vote_average()
    {
        return $this->data['vote_average'];
    }

    public function get_vote_count()
    {
        return $this->data['vote_count'];
    }

    public function get_trailer(): ?string
    {
        if ($this->data['videos']['results']) {
            return 'https://www.youtube-nocookie.com/embed/'.$this->data['videos']['results'][0]['key'];
        }

        return null;
    }

    public function get_videos(): ?string
    {
        if ($this->data['videos']['results']) {
            return 'https://www.youtube.com/embed/'.$this->data['videos']['results'];
        }

        return null;
    }

    public function get_images()
    {
        return $this->data['images']['results'];
    }

    public function get_cast()
    {
        return $this->data['credits']['cast'];
    }

    public function get_crew()
    {
        return $this->data['credits']['crew'];
    }
}
