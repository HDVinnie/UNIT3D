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

use App\Models\PrivateMessage;
use App\Models\Warning;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoDeactivateWarningTest
 */
class AutoDeactivateWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:deactivate_warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Deactivates User Warnings If Expired';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $current = Carbon::now();
        $warnings = Warning::with(['warneduser', 'torrenttitle'])->where('active', '=', 1)->where('expires_on', '<', $current)->get();

        foreach ($warnings as $warning) {
            // Set Records Active To 0 in warnings table
            $warning->active = '0';
            $warning->save();

            // Send Private Message
            $pm = new PrivateMessage();
            $pm->sender_id = 1;
            $pm->receiver_id = $warning->warneduser->id;
            $pm->subject = 'Hit and Run Warning Deactivated';
            $pm->message = 'The [b]WARNING[/b] you received relating to Torrent '.$warning->torrenttitle->name.' has expired! Try not to get more! [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
            $pm->save();
        }
        $this->comment('Automated Warning Deativation Command Complete');
    }
}
