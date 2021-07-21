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

use App\Models\Invite;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoRecycleInvitesTest
 */
class AutoRecycleInvites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:recycle_invites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recycle Invites That Are Expired.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $invites = Invite::whereNull('accepted_by')->whereNull('accepted_at')->where('expires_on', '<', $current)->get();

        foreach ($invites as $invite) {
            $invite->delete();
        }
        $this->comment('Automated Purge Unaccepted Invites Command Complete');
    }
}
