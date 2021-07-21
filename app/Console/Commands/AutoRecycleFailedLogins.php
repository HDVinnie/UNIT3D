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

use App\Models\FailedLoginAttempt;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoRecycleFailedLoginsTest
 */
class AutoRecycleFailedLogins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:recycle_failed_logins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recycle Failed Logins Once 30 Days Old.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $failedLogins = FailedLoginAttempt::where('created_at', '<', $current->copy()->subDays(30)->toDateTimeString())->get();

        foreach ($failedLogins as $failedLogin) {
            $failedLogin->delete();
        }
        $this->comment('Automated Purge Old Failed Logins Command Complete');
    }
}
