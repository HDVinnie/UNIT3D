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

use App\Models\Subtitle;
use App\Models\Torrent;
use Livewire\Component;
use Livewire\WithPagination;

class SubtitleSearch extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public array $categories = [];
    public string $language = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    final public function paginationView(): string
    {
        return 'vendor.pagination.livewire-pagination';
    }

    final public function updatingSearch(): void
    {
        $this->resetPage();
    }

    final public function getSubtitlesProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Subtitle::with(['user', 'torrent', 'language'])
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->categories, function ($query) {
                $torrents = Torrent::whereIn('category_id', $this->categories)->pluck('id');

                return $query->whereIn('torrent_id', $torrents);
            })
            ->when($this->language, fn ($query) => $query->where('language_id', '=', $this->language))
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
        return \view('livewire.subtitle-search', [
            'subtitles' => $this->subtitles,
        ]);
    }
}
