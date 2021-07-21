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

namespace App\Console\Commands;

use App\Models\Torrent;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\SyncPeersTest
 */
class SyncPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:sync_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrects Torrent Seeders/Leechers (Peers) Count Due To Not Receiving A STOPPED Event From Client.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $torrents = Torrent::select(['id', 'seeders', 'leechers'])
            ->with('peers')
            ->withAnyStatus()
            ->get();

        foreach ($torrents as $torrent) {
            $torrent->seeders = $torrent->peers->where('left', '=', '0')->count();
            $torrent->leechers = $torrent->peers->where('left', '>', '0')->count();
            $torrent->save();
        }
        $this->comment('Torrent Peer Syncing Command Complete');
    }
}
