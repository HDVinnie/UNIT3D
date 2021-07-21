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

use App\Jobs\ProcessCollectionJob;
use App\Jobs\ProcessCompanyJob;
use App\Jobs\ProcessMovieJob;
use App\Jobs\ProcessTvJob;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Movie;
use App\Models\Tv;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TMDBScraper implements ShouldQueue
{
    /**
     * @var mixed|array|string|null
     */
    public mixed $id;
    use SerializesModels;

    public function __construct(Request $request = null)
    {
        if ($request != null) {
            $this->id = $request->query('id');
        }
    }

    public function tv($id = null): void
    {
        if ($id == null) {
            $id = $this->id;
        }

        $tmdb = new TMDB();
        $tv = (new Client\TV($id))->getData();
        if (isset($tv['id'])) {
            $array = [
                'backdrop'           => $tmdb->image('backdrop', $tv),
                'episode_run_time'   => $tmdb->ifHasItems('episode_run_time', $tv),
                'first_air_date'     => $tmdb->ifExists('first_air_date', $tv),
                'homepage'           => $tv['homepage'],
                'in_production'      => $tv['in_production'],
                'last_air_date'      => $tmdb->ifExists('last_air_date', $tv),
                'name'               => Str::limit($tv['name'], 200),
                'name_sort'          => \addslashes(\str_replace(['The ', 'An ', 'A ', '"'], [''], Str::limit($tv['name'], 100))),
                'number_of_episodes' => $tv['number_of_episodes'],
                'number_of_seasons'  => $tv['number_of_seasons'],
                'origin_country'     => $tmdb->ifHasItems('origin_country', $tv),
                'original_language'  => $tv['original_language'],
                'original_name'      => $tv['original_name'],
                'overview'           => $tv['overview'],
                'popularity'         => $tv['popularity'],
                'poster'             => $tmdb->image('poster', $tv),
                'status'             => $tv['status'],
                'vote_average'       => $tv['vote_average'],
                'vote_count'         => $tv['vote_count'],
            ];

            Tv::updateOrCreate(['id' => $id], $array);

            ProcessTvJob::dispatch($tv, $id);

            //return ['message' => 'Tv with id: ' . $id . ' Has been added  to the database, But episodes are loaded with the queue'];
        }
    }

    public function movie($id = null): void
    {
        if ($id == null) {
            $id = $this->id;
        }

        $tmdb = new TMDB();
        $movie = (new Client\Movie($id))->getData();

        if (\array_key_exists('title', $movie)) {
            $re = '/((?<namesort>.*)(?<seperator>\:|and)(?<remaining>.*)|(?<name>.*))/m';
            \preg_match($re, $movie['title'], $matches);

            $year = (new DateTime($movie['release_date']))->format('Y');
            $titleSort = \addslashes(\str_replace(['The ', 'An ', 'A ', '"'], [''],
                Str::limit($matches['namesort'] ? $matches['namesort'].' '.$year : $movie['title'], 100)));

            $array = [
                'adult'             => $movie['adult'] ?? 0,
                'backdrop'          => $tmdb->image('backdrop', $movie),
                'budget'            => $movie['budget'] ?? null,
                'homepage'          => $movie['homepage'] ?? null,
                'imdb_id'           => $movie['imdb_id'] ?? null,
                'original_language' => $movie['original_language'] ?? null,
                'original_title'    => $movie['original_title'] ?? null,
                'overview'          => $movie['overview'] ?? null,
                'popularity'        => $movie['popularity'] ?? null,
                'poster'            => $tmdb->image('poster', $movie),
                'release_date'      => $tmdb->ifExists('release_date', $movie),
                'revenue'           => $movie['revenue'] ?? null,
                'runtime'           => $movie['runtime'] ?? null,
                'status'            => $movie['status'] ?? null,
                'tagline'           => $movie['tagline'] ?? null,
                'title'             => Str::limit($movie['title'], 200),
                'title_sort'        => $titleSort,
                'vote_average'      => $movie['vote_average'] ?? null,
                'vote_count'        => $movie['vote_count'] ?? null,
            ];

            Movie::updateOrCreate(['id' => $movie['id']], $array);

            ProcessMovieJob::dispatch($movie, $id);

            //return ['message' => 'Movies with id: ' . $id . ' Has been added  to the database, But relations are loaded with the queue'];
        }
    }

    public function collection($id = null): void
    {
        if ($id == null) {
            $id = $this->id;
        }

        $tmdb = new TMDB();
        $collection = (new Client\Collection($id))->getData();

        $array = [
            'name'     => $collection['name'],
            'overview' => $collection['overview'],
            'backdrop' => $tmdb->image('backdrop', $collection),
            'poster'   => $tmdb->image('poster', $collection),
        ];
        Collection::updateOrCreate(['id' => $collection['id']], $array);

        ProcessCollectionJob::dispatch($collection);

        //return ['message' => 'Collection with id: ' . $id . ' Has been added  to the database, But movies are loaded with the queue'];
    }

    public function company($id = null): void
    {
        if ($id == null) {
            $id = $this->id;
        }

        $tmdb = new TMDB();
        $company = (new Client\Company($id))->getData();

        $array = [
            'name'     => $company['name'],
            'overview' => $company['overview'],
            'backdrop' => $tmdb->image('backdrop', $company),
            'poster'   => $tmdb->image('poster', $company),
        ];
        Company::updateOrCreate(['id' => $company['id']], $array);

        ProcessCompanyJob::dispatch($company);

        //return ['message' => 'Company with id: ' . $id . ' Has been added  to the database, But movies are loaded with the queue'];
    }
}
