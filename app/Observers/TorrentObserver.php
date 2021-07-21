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

namespace App\Observers;

use App\Models\Torrent;

class TorrentObserver
{
    /**
     * Handle the Torrent "created" event.
     */
    public function created(Torrent $torrent): void
    {
        //\cache()->put(\sprintf('torrent:%s', $torrent->info_hash), $torrent);
    }

    /**
     * Handle the Torrent "updated" event.
     */
    public function updated(Torrent $torrent): void
    {
        //\cache()->put(\sprintf('torrent:%s', $torrent->info_hash), $torrent);
    }

    /**
     * Handle the Torrent "retrieved" event.
     */
    public function retrieved(Torrent $torrent): void
    {
        //\cache()->add(\sprintf('torrent:%s', $torrent->info_hash), $torrent);
    }

    /**
     * Handle the Torrent "deleted" event.
     */
    public function deleted(Torrent $torrent): void
    {
        //\cache()->forget(\sprintf('torrent:%s', $torrent->info_hash));
    }

    /**
     * Handle the Torrent "restored" event.
     */
    public function restored(Torrent $torrent): void
    {
        //\cache()->put(\sprintf('torrent:%s', $torrent->info_hash), $torrent);
    }
}
