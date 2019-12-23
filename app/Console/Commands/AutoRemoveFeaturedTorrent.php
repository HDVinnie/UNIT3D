<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Console\Commands;

use Illuminate\Contracts\Config\Repository;
use App\Models\FeaturedTorrent;
use App\Models\Torrent;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class AutoRemoveFeaturedTorrent extends Command
{
    /**
     * @var ChatRepository
     */
    private ChatRepository $chat;
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $configRepository;

    public function __construct(ChatRepository $chat, Repository $configRepository)
    {
        parent::__construct();

        $this->chat = $chat;
        $this->configRepository = $configRepository;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'auto:remove_featured_torrent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Automatically Removes Featured Torrents If Expired';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $featured_torrents = FeaturedTorrent::where('created_at', '<', $current->copy()->subDays(7)->toDateTimeString())->get();

        foreach ($featured_torrents as $featured_torrent) {
            // Find The Torrent
            $torrent = Torrent::where('featured', '=', 1)->where('id', '=', $featured_torrent->torrent_id)->first();
            $torrent->free = 0;
            $torrent->doubleup = 0;
            $torrent->featured = 0;
            $torrent->save();

            // Auto Announce Featured Expired
            $appurl = $this->configRepository->get('app.url');

            $this->chat->systemMessage(
                sprintf('Ladies and Gents, [url=%s/torrents/%s]%s[/url] is no longer featured. :poop:', $appurl, $torrent->id, $torrent->name)
            );

            // Delete The Record From DB
            $featured_torrent->delete();
        }
    }
}
