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

use App\Models\Peer;
use App\Models\Seedbox;
use App\Models\Torrent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Unit\Console\Commands\AutoHighspeedTagTest
 */
class AutoHighspeedTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:highspeed_tag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Torrents Highspeed Tag Based On Registered Seedboxes.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        DB::table('torrents')->update(['highspeed' => 0]);

        $seedboxUsers = Seedbox::select(['user_id'])->get()->toArray();

        if (\is_array($seedboxUsers) && \count($seedboxUsers) > 0) {
            $torid = Peer::select(['torrent_id'])->whereIn('user_id', $seedboxUsers)->where('seeder', '=', 1)->get()->toArray();

            foreach ($torid as $id) {
                $torrent = Torrent::where('id', '=', $id)->first();
                if (isset($torrent)) {
                    $torrent->highspeed = 1;
                    $torrent->save();
                }

                unset($torrent);
            }
        }
        $this->comment('Automated High Speed Torrents Command Complete');
    }
}
