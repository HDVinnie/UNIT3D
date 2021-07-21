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

use App\Models\FeaturedTorrent;
use App\Models\Graveyard;
use App\Models\History;
use App\Models\Peer;
use App\Models\PersonalFreeleech;
use App\Models\PlaylistTorrent;
use App\Models\PrivateMessage;
use App\Models\Subtitle;
use App\Models\Torrent;
use App\Models\TorrentFile;
use App\Models\TorrentRequest;
use App\Models\Warning;
use Livewire\Component;

class SimilarTorrent extends Component
{
    public $categoryId;
    public $tmdbId;
    public $reason;
    public array $checked = [];
    public bool $selectPage = false;
    public bool $selectAll = false;
    public string $sortField = 'bumped_at';
    public string $sortDirection = 'desc';

    protected $listeners = ['destroy' => 'deleteRecords'];

    final public function updatedSelectPage($value): void
    {
        if ($value) {
            $this->checked = $this->torrents->pluck('id')->map(fn ($item) => (string) $item)->toArray();
        } else {
            $this->checked = [];
        }
    }

    final public function selectAll(): void
    {
        $this->selectAll = true;
        $this->checked = $this->torrents->pluck('id')->map(fn ($item) => (string) $item)->toArray();
    }

    final public function updatedChecked(): void
    {
        $this->selectPage = false;
    }

    final public function isChecked($torrentId): bool
    {
        return in_array($torrentId, $this->checked);
    }

    final public function getTorrentsProperty(): \Illuminate\Database\Eloquent\Collection | array
    {
        return Torrent::with(['user:id,username,group_id', 'category', 'type', 'resolution'])
            ->withCount(['thanks', 'comments'])
            ->where('category_id', '=', $this->categoryId)
            ->where('tmdb', '=', $this->tmdbId)
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
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

    final public function alertConfirm(): void
    {
        $torrents = Torrent::whereKey($this->checked)->pluck('name');
        $names = [];
        foreach ($torrents as $torrent) {
            $names[] = $torrent;
        }
        $this->dispatchBrowserEvent('swal:confirm', [
            'type'    => 'warning',
            'message' => 'Are you sure?',
            'body'    => 'If deleted, you will not be able to recover the following files!'.\nl2br("\n")
                        .\nl2br(\implode("\n", $names)),
        ]);
    }

    final public function deleteRecords(): void
    {
        $torrents = Torrent::whereKey($this->checked)->get();
        $names = [];
        foreach ($torrents as $torrent) {
            $names[] = $torrent->name;
            foreach (History::where('info_hash', '=', $torrent->info_hash)->get() as $pm) {
                $pmuser = new PrivateMessage();
                $pmuser->sender_id = 1;
                $pmuser->receiver_id = $pm->user_id;
                $pmuser->subject = 'Bulk Torrents Deleted!';
                $pmuser->message = '[b]Attention: [/b] The following torrents '.\implode(', ', $names).' have been removed from our site. Our system shows that you were either the uploader, a seeder or a leecher on said torrent. We just wanted to let you know you can safely remove it from your client.
                                    [b]Removal Reason: [/b] '.$this->reason.'
                                    [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
                $pmuser->save();
            }

            // Reset Requests
            $torrentRequest = TorrentRequest::where('filled_hash', '=', $torrent->info_hash)->get();
            foreach ($torrentRequest as $req) {
                if ($req) {
                    $req->filled_by = null;
                    $req->filled_when = null;
                    $req->filled_hash = null;
                    $req->approved_by = null;
                    $req->approved_when = null;
                    $req->save();
                }
            }

            //Remove Torrent related info
            \cache()->forget(\sprintf('torrent:%s', $torrent->info_hash));
            Peer::where('torrent_id', '=', $torrent->id)->delete();
            History::where('info_hash', '=', $torrent->info_hash)->delete();
            Warning::where('torrent', '=', $torrent->id)->delete();
            TorrentFile::where('torrent_id', '=', $torrent->id)->delete();
            PlaylistTorrent::where('torrent_id', '=', $torrent->id)->delete();
            Subtitle::where('torrent_id', '=', $torrent->id)->delete();
            Graveyard::where('torrent_id', '=', $torrent->id)->delete();
            if ($torrent->featured === 1) {
                FeaturedTorrent::where('torrent_id', '=', $torrent->id)->delete();
            }

            $torrent->delete();
        }

        $this->checked = [];
        $this->selectAll = false;
        $this->selectPage = false;

        $this->dispatchBrowserEvent('swal:modal', [
            'type'    => 'success',
            'message' => 'Torrents Deleted Successfully!',
            'text'    => 'A personal message has been sent to all users that have downloaded these torrents.',
        ]);
    }

    final public function deleteSingleRecord($torrentId): void
    {
        $torrent = Torrent::findOrFail($torrentId);
        $torrent->delete();
        $this->checked = array_diff($this->checked, [$torrentId]);

        $this->dispatchBrowserEvent('swal:modal', [
            'type'    => 'success',
            'message' => 'Torrent Deleted Successfully!',
            'text'    => 'A personal message has been sent to all users that have downloaded this torrent.',
        ]);
    }

    final public function getPersonalFreeleechProperty()
    {
        return PersonalFreeleech::where('user_id', '=', \auth()->user()->id)->first();
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.similar-torrent', [
            'user'              => \auth()->user(),
            'torrents'          => $this->torrents,
            'personalFreeleech' => $this->personalFreeleech,
        ]);
    }
}
