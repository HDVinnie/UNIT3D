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

use App\Models\Audit;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoRecycleAuditsTest
 */
class AutoRecycleAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:recycle_activity_log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recycle Activity From Log Once 30 Days Old.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $audits = Audit::where('created_at', '<', $current->copy()->subDays(\config('audit.recycle'))->toDateTimeString())->get();

        foreach ($audits as $audit) {
            $audit->delete();
        }
        $this->comment('Automated Purge Old Audits Command Complete');
    }
}
