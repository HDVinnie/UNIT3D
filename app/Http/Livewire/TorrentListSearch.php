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

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Keyword;
use App\Models\PersonalFreeleech;
use App\Models\PlaylistTorrent;
use App\Models\Torrent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TorrentListSearch extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public string $mediainfo = '';
    public string $uploader = '';
    public array $keywords = [];
    public string $startYear = '';
    public string $endYear = '';
    public array $categories = [];
    public array $types = [];
    public array $resolutions = [];
    public array $genres = [];
    public string $tmdbId = '';
    public string $imdbId = '';
    public string $tvdbId = '';
    public string $malId = '';
    public string $playlistId = '';
    public string $collectionId = '';
    public $free;
    public $doubleup;
    public $featured;
    public $stream;
    public $sd;
    public $highspeed;
    public $internal;
    public $personalRelease;
    public $alive;
    public $dying;
    public $dead;

    public int $perPage = 25;
    public string $sortField = 'bumped_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'name'            => ['except' => ''],
        'description'     => ['except' => ''],
        'mediainfo'       => ['except' => ''],
        'uploader'        => ['except' => ''],
        'keywords'        => ['except' => []],
        'startYear'       => ['except' => ''],
        'endYear'         => ['except' => ''],
        'categories'      => ['except' => []],
        'types'           => ['except' => []],
        'resolutions'     => ['except' => []],
        'genres'          => ['except' => []],
        'tmdbId'          => ['except' => ''],
        'imdbId'          => ['except' => ''],
        'tvdbId'          => ['except' => ''],
        'malId'           => ['except' => ''],
        'playlistId'      => ['except' => ''],
        'collectionId'    => ['except' => ''],
        'free'            => ['except' => false],
        'doubleup'        => ['except' => false],
        'featured'        => ['except' => false],
        'stream'          => ['except' => false],
        'sd'              => ['except' => false],
        'highspeed'       => ['except' => false],
        'internal'        => ['except' => false],
        'personalRelease' => ['except' => false],
        'alive'           => ['except' => false],
        'dying'           => ['except' => false],
        'dead'            => ['except' => false],
        'sortField'       => ['except' => 'bumped_at'],
        'sortDirection'   => ['except' => 'desc'],
        'page'            => ['except' => 1],
        'perPage'         => ['except' => ''],
    ];

    protected array $rules = [
        'genres.*' => 'exists:genres,id',
    ];

    final public function paginationView(): string
    {
        return 'vendor.pagination.livewire-pagination';
    }

    final public function updatingName(): void
    {
        $this->resetPage();
    }

    final public function getTorrentsStatProperty(): \Illuminate\Database\Eloquent\Model | object | \Illuminate\Database\Query\Builder | null
    {
        return DB::table('torrents')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when seeders > 0 then 1 end) as alive')
            ->selectRaw('count(case when seeders = 0 then 1 end) as dead')
            ->first();
    }

    final public function getPersonalFreeleechProperty()
    {
        return PersonalFreeleech::where('user_id', '=', \auth()->user()->id)->first();
    }

    final public function getTorrentsProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Torrent::with(['user:id,username,group_id', 'category', 'type', 'resolution'])
            ->withCount(['thanks', 'comments'])
            ->when($this->name, function ($query) {
                $terms = \explode(' ', $this->name);
                $search = '';
                foreach ($terms as $term) {
                    $search .= '%'.$term.'%';
                }
                $query->where('name', 'LIKE', $search);
            })
            ->when($this->description, function ($query) {
                $query->where('description', 'LIKE', '%'.$this->description.'%');
            })
            ->when($this->mediainfo, function ($query) {
                $query->where('mediainfo', 'LIKE', '%'.$this->mediainfo.'%');
            })
            ->when($this->uploader, function ($query) {
                $match = User::where('username', 'LIKE', '%'.$this->uploader.'%')->orderBy('username', 'ASC')->first();
                if ($match) {
                    $query->where('user_id', '=', $match->id)->where('anon', '=', 0);
                }
            })
            ->when($this->keywords, function ($query) {
                $keywords = self::parseKeywords($this->keywords);
                $keyword = Keyword::select(['torrent_id'])->whereIn('name', $keywords)->get();
                $query->whereIn('id', $keyword->torrent_id);
            })
            ->when($this->startYear && $this->endYear, function ($query) {
                $query->whereBetween('release_year', [$this->startYear, $this->endYear]);
            })
            ->when($this->categories, function ($query) {
                $query->whereIn('category_id', $this->categories);
            })
            ->when($this->types, function ($query) {
                $query->whereIn('type_id', $this->types);
            })
            ->when($this->resolutions, function ($query) {
                $query->whereIn('resolution_id', $this->resolutions);
            })
            ->when($this->genres, function ($query) {
                $this->validate();

                $tvCollection = DB::table('genre_tv')->whereIn('genre_id', $this->genres)->pluck('tv_id');
                $movieCollection = DB::table('genre_movie')->whereIn('genre_id', $this->genres)->pluck('movie_id');
                $mergedCollection = $tvCollection->merge($movieCollection);

                $query->whereRaw("tmdb in ('".\implode("','", $mergedCollection->toArray())."')"); // Protected with Validation that IDs passed are not malicious
                //$query->whereIn('tmdb', $mergedCollection); Very SLOW!
            })
            ->when($this->tmdbId === '0' || $this->tmdbId, function ($query) {
                $query->where('tmdb', '=', $this->tmdbId);
            })
            ->when($this->imdbId === '0' || $this->imdbId, function ($query) {
                $query->where('imdb', '=', $this->imdbId);
            })
            ->when($this->tvdbId === '0' || $this->tvdbId, function ($query) {
                $query->where('tvdb', '=', $this->tvdbId);
            })
            ->when($this->malId === '0' || $this->malId, function ($query) {
                $query->where('mal', '=', $this->malId);
            })
            ->when($this->playlistId, function ($query) {
                $playlist = PlaylistTorrent::where('playlist_id', '=', $this->playlistId)->pluck('torrent_id');
                $query->whereIn('id', $playlist);
            })
            ->when($this->collectionId, function ($query) {
                $categories = Category::where('movie_meta', '=', 1)->pluck('id');
                $collection = DB::table('collection_movie')->where('collection_id', '=', $this->collectionId)->pluck('movie_id');
                $query->whereIn('category_id', $categories)->whereIn('tmdb', $collection);
            })
            ->when($this->free, function ($query) {
                $query->where('free', '=', 1);
            })
            ->when($this->doubleup, function ($query) {
                $query->where('doubleup', '=', 1);
            })
            ->when($this->featured, function ($query) {
                $query->where('featured', '=', 1);
            })
            ->when($this->stream, function ($query) {
                $query->where('stream', '=', 1);
            })
            ->when($this->sd, function ($query) {
                $query->where('sd', '=', 1);
            })
            ->when($this->highspeed, function ($query) {
                $query->where('highspeed', '=', 1);
            })
            ->when($this->internal, function ($query) {
                $query->where('internal', '=', 1);
            })
            ->when($this->personalRelease, function ($query) {
                $query->where('personal_release', '=', 1);
            })
            ->when($this->alive, function ($query) {
                $query->where('seeders', '>=', 1);
            })
            ->when($this->dying, function ($query) {
                $query->where('seeders', '=', 1)->where('times_completed', '>=', 3);
            })
            ->when($this->dead, function ($query) {
                $query->where('seeders', '=', 0);
            })
            ->orderBy('sticky', 'desc')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    private static function parseKeywords($text): array
    {
        $parts = \explode(', ', $text);
        $result = [];
        foreach ($parts as $part) {
            $part = \trim($part);
            if ($part != '') {
                $result[] = $part;
            }
        }

        return $result;
    }

    final public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.torrent-list-search', [
            'user'              => User::with('history')->findOrFail(\auth()->user()->id),
            'torrents'          => $this->torrents,
            'torrentsStat'      => $this->torrentsStat,
            'personalFreeleech' => $this->personalFreeleech,
        ]);
    }
}
