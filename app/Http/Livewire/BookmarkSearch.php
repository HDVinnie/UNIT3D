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

use App\Models\PersonalFreeleech;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class BookmarkSearch extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public $user;

    final public function mount(): void
    {
        $this->user = \auth()->user();
    }

    final public function paginationView(): string
    {
        return 'vendor.pagination.livewire-pagination';
    }

    final public function updatingSearch(): void
    {
        $this->resetPage();
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

    final public function getUserProperty()
    {
        return User::where('username', '=', $this->user->username)->firstOrFail();
    }

    final public function getBookmarksProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->user->bookmarks()
            ->when($this->search, fn ($query) => $query->where('name', 'LIKE', '%'.$this->search.'%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    final public function getPersonalFreeleechProperty()
    {
        return PersonalFreeleech::where('user_id', '=', $this->user->id)->first();
    }

    public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.bookmark-search', [
            'user'               => $this->user,
            'personal_freeleech' => $this->personalFreeleech,
            'bookmarks'          => $this->bookmarks,
        ]);
    }
}
