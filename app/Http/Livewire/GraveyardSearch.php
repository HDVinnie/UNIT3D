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

use App\Models\Torrent;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class GraveyardSearch extends Component
{
    use WithPagination;

    public string $name = '';
    public array $categories = [];
    public array $types = [];
    public array $resolutions = [];
    public string $tmdbId = '';
    public string $imdbId = '';
    public string $tvdbId = '';
    public string $malId = '';
    public $free;
    public $doubleup;
    public $featured;
    public $stream;
    public $sd;
    public $highspeed;
    public $internal;

    public int $perPage = 25;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $showFilters = false;

    protected $queryString = [
        'name'          => ['except' => ''],
        'categories'    => ['except' => []],
        'types'         => ['except' => []],
        'resolutions'   => ['except' => []],
        'tmdbId'        => ['except' => ''],
        'imdbId'        => ['except' => ''],
        'tvdbId'        => ['except' => ''],
        'malId'         => ['except' => ''],
        'free'          => ['except' => false],
        'doubleup'      => ['except' => false],
        'featured'      => ['except' => false],
        'stream'        => ['except' => false],
        'sd'            => ['except' => false],
        'highspeed'     => ['except' => false],
        'internal'      => ['except' => false],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'page'          => ['except' => 1],
    ];

    final public function paginationView(): string
    {
        return 'vendor.pagination.livewire-pagination';
    }

    final public function toggleShowFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    final public function getTorrentsStatProperty()
    {
        return Torrent::where('seeders', '=', 0)
            ->where('created_at', '<', Carbon::now()->copy()->subDays(30)->toDateTimeString())
            ->count();
    }

    final public function getTorrentsProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Torrent::with('category', 'type', 'resolution')
            ->where('created_at', '<', Carbon::now()->copy()->subDays(30)->toDateTimeString())
            ->where('seeders', '=', 0)
            ->when($this->name, function ($query) {
                $query->where('name', 'LIKE', '%'.$this->name.'%');
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
            ->when($this->tmdbId, function ($query) {
                $query->where('tmdb', '=', $this->tmdbId);
            })
            ->when($this->imdbId, function ($query) {
                $query->where('imdb', '=', $this->imdbId);
            })
            ->when($this->tvdbId, function ($query) {
                $query->where('tvdb', '=', $this->tvdbId);
            })
            ->when($this->malId, function ($query) {
                $query->where('mal', '=', $this->malId);
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
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
        return \view('livewire.graveyard-search', [
            'user'         => \auth()->user(),
            'torrents'     => $this->torrents,
            'torrentsStat' => $this->torrentsStat,
        ]);
    }
}
