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

use App\Models\TorrentRequest;
use App\Models\TorrentRequestClaim;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoRecycleClaimedTorrentRequestsTest
 */
class AutoRecycleClaimedTorrentRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:recycle_claimed_torrent_requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recycle Torrent Requests That Wwere Claimed But Not Filled Within 7 Days.';

    /**
     * AutoRecycleClaimedTorrentRequests Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     *@throws \Exception
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $torrentRequests = TorrentRequest::where('claimed', '=', 1)
            ->whereNull('filled_by')
            ->whereNull('filled_when')
            ->whereNull('filled_hash')
            ->get();

        foreach ($torrentRequests as $torrentRequest) {
            $requestClaim = TorrentRequestClaim::where('request_id', '=', $torrentRequest->id)
                ->where('created_at', '<', $current->copy()->subDays(7)->toDateTimeString())
                ->first();
            if ($requestClaim) {
                $trUrl = \href_request($torrentRequest);
                $this->chatRepository->systemMessage(
                    \sprintf('[url=%s]%s[/url] claim has been reset due to not being filled within 7 days.', $trUrl, $torrentRequest->name)
                );

                $requestClaim->delete();
                $torrentRequest->claimed = null;
                $torrentRequest->save();
            }
        }
        $this->comment('Automated Request Claim Reset Command Complete');
    }
}
