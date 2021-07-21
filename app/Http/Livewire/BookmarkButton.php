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
use Livewire\Component;

class BookmarkButton extends Component
{
    public $torrent;
    public $user;

    final public function mount($torrent): void
    {
        $this->user = \auth()->user();
        $this->torrent = Torrent::withAnyStatus()->findOrFail($torrent);
    }

    final public function getIsBookmarkedProperty(): int
    {
        return $this->torrent->bookmarked() ? 1 : 0;
    }

    final public function store(): void
    {
        if ($this->user->isBookmarked($this->torrent->id)) {
            $this->dispatchBrowserEvent('error', ['type' => 'error',  'message' => 'Torrent Has Already Been Bookmarked!']);

            return;
        }

        $this->user->bookmarks()->attach($this->torrent->id);
        $this->dispatchBrowserEvent('success', ['type' => 'success',  'message' => 'Torrent Has Been Bookmarked Successfully!']);
    }

    final public function destroy(): void
    {
        $this->user->bookmarks()->detach($this->torrent->id);
        $this->dispatchBrowserEvent('success', ['type' => 'success',  'message' => 'Torrent Has Been Unbookmarked Successfully!']);
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.bookmark-button');
    }
}
