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

namespace App\Jobs;

use App\Exceptions\TrackerException;
use App\Models\FreeleechToken;
use App\Models\Group;
use App\Models\History;
use App\Models\Peer;
use App\Models\PersonalFreeleech;
use App\Models\Torrent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCompletedAnnounceRequest implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * ProcessCompletedAnnounceRequest Constructor.
     *
     * @param $queries
     */
    public function __construct(protected $queries, protected User $user, protected Torrent $torrent)
    {
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle()
    {
        // Get The Current Peer
        $peer = Peer::where('torrent_id', '=', $this->torrent->id)
            ->where('peer_id', $this->queries['peer_id'])
            ->where('user_id', '=', $this->user->id)
            ->first();

        // Flag is tripped if new session is created but client reports up/down > 0
        $ghost = false;
        if ($peer === null && \strtolower($this->queries['event']) === 'completed') {
            throw new TrackerException(151);
        }

        // Creates a new peer if not existing
        if ($peer === null) {
            if ($this->queries['uploaded'] > 0 || $this->queries['downloaded'] > 0) {
                $ghost = true;
                $this->queries['event'] = 'started';
            }
            $peer = new Peer();
        }

        // Get history information
        $history = History::where('info_hash', '=', $this->queries['info_hash'])->where('user_id', '=', $this->user->id)->first();

        // If no History record found then create one
        if ($history === null) {
            $history = new History();
            $history->user_id = $this->user->id;
            $history->info_hash = $this->queries['info_hash'];
        }

        $realUploaded = $this->queries['uploaded'];
        $realDownloaded = $this->queries['downloaded'];

        if ($ghost) {
            $uploaded = ($realUploaded >= $history->client_uploaded) ? ($realUploaded - $history->client_uploaded) : 0;
            $downloaded = ($realDownloaded >= $history->client_downloaded) ? ($realDownloaded - $history->client_downloaded) : 0;
        } else {
            $uploaded = ($realUploaded >= $peer->uploaded) ? ($realUploaded - $peer->uploaded) : 0;
            $downloaded = ($realDownloaded >= $peer->downloaded) ? ($realDownloaded - $peer->downloaded) : 0;
        }

        $oldUpdate = $peer->updated_at->timestamp ?? Carbon::now()->timestamp;

        // Modification of Upload and Download
        $personalFreeleech = PersonalFreeleech::where('user_id', '=', $this->user->id)->first();
        $freeleechToken = FreeleechToken::where('user_id', '=', $this->user->id)->where('torrent_id', '=', $this->torrent->id)->first();
        $group = Group::whereId($this->user->group_id)->first();

        if (\config('other.freeleech') == 1 || $this->torrent->free == 1 || $personalFreeleech || $group->is_freeleech == 1 || $freeleechToken) {
            $modDownloaded = 0;
        } else {
            $modDownloaded = $downloaded;
        }

        if (\config('other.doubleup') == 1 || $this->torrent->doubleup == 1 || $group->is_double_upload == 1) {
            $modUploaded = $uploaded * 2;
        } else {
            $modUploaded = $uploaded;
        }

        // Peer Update
        $peer->peer_id = $this->queries['peer_id'];
        $peer->md5_peer_id = \md5($this->queries['peer_id']);
        $peer->info_hash = $this->queries['info_hash'];
        $peer->ip = $this->queries['ip-address'];
        $peer->port = $this->queries['port'];
        $peer->agent = $this->queries['user-agent'];
        $peer->uploaded = $realUploaded;
        $peer->downloaded = $realDownloaded;
        $peer->seeder = 1;
        $peer->left = 0;
        $peer->torrent_id = $this->torrent->id;
        $peer->user_id = $this->user->id;
        $peer->save();
        // End Peer Update

        // History Update
        $history->agent = $this->queries['user-agent'];
        $history->active = 1;
        $history->seeder = $this->queries['left'] == 0;
        $history->uploaded += $modUploaded;
        $history->actual_uploaded += $uploaded;
        $history->client_uploaded = $realUploaded;
        $history->downloaded += $modDownloaded;
        $history->actual_downloaded += $downloaded;
        $history->client_downloaded = $realDownloaded;
        $history->completed_at = Carbon::now();
        // Seedtime allocation
        if ($this->queries['left'] == 0) {
            $newUpdate = $peer->updated_at->timestamp;
            $diff = $newUpdate - $oldUpdate;
            $history->seedtime += $diff;
        }
        $history->save();
        // End History Update

        // User Update
        $this->user->uploaded += $modUploaded;
        $this->user->downloaded += $modDownloaded;
        $this->user->save();
        // End User Update

        // Torrent Completed Update
        $this->torrent->increment('times_completed');

        // Sync Seeders / Leechers Count
        $this->torrent->seeders = Peer::where('torrent_id', '=', $this->torrent->id)->where('left', '=', '0')->count();
        $this->torrent->leechers = Peer::where('torrent_id', '=', $this->torrent->id)->where('left', '>', '0')->count();
        $this->torrent->save();
    }
}
