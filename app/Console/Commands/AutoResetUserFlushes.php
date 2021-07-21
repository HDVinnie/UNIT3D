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

use App\Models\User;
use Illuminate\Console\Command;

class AutoResetUserFlushes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:reset_user_flushes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the daily limit for users to flush their own peers.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Updates own_flushes for each user
        User::where('own_flushes', '<', '2')->update(['own_flushes' => '2']);

        $this->comment('Automated Reset User Flushes Command Complete');
    }
}
