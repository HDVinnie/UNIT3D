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

use App\Models\FeaturedTorrent;
use App\Models\Torrent;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoRemoveFeaturedTorrentTest
 */
class AutoRemoveFeaturedTorrent extends Command
{
    /**
     * AutoRemoveFeaturedTorrent Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:remove_featured_torrent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Removes Featured Torrents If Expired';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $featuredTorrents = FeaturedTorrent::where('created_at', '<', $current->copy()->subDays(7)->toDateTimeString())->get();

        foreach ($featuredTorrents as $featuredTorrent) {
            // Find The Torrent
            $torrent = Torrent::where('featured', '=', 1)->where('id', '=', $featuredTorrent->torrent_id)->first();
            if (isset($torrent)) {
                $torrent->free = 0;
                $torrent->doubleup = 0;
                $torrent->featured = 0;
                $torrent->save();

                // Auto Announce Featured Expired
                $appurl = \config('app.url');

                $this->chatRepository->systemMessage(
                    \sprintf('Ladies and Gents, [url=%s/torrents/%s]%s[/url] is no longer featured. :poop:', $appurl, $torrent->id, $torrent->name)
                );
            }

            // Delete The Record From DB
            $featuredTorrent->delete();
        }
        $this->comment('Automated Removal Featured Torrents Command Complete');
    }
}
