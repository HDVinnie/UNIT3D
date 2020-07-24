<?php
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

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('auto:group ')->daily();
        $schedule->command('auto:nerdstat ')->hourly();
        $schedule->command('auto:graveyard')->daily();
        $schedule->command('auto:highspeed_tag')->hourly();
        $schedule->command('auto:prewarning')->hourly();
        $schedule->command('auto:warning')->daily();
        $schedule->command('auto:deactivate_warning')->hourly();
        $schedule->command('auto:revoke_permissions')->hourly();
        $schedule->command('auto:ban')->hourly();
        $schedule->command('auto:flush_peers')->hourly();
        $schedule->command('auto:bon_allocation')->hourly();
        $schedule->command('auto:remove_personal_freeleech')->hourly();
        $schedule->command('auto:remove_featured_torrent')->hourly();
        $schedule->command('auto:recycle_invites')->daily();
        $schedule->command('auto:recycle_activity_log')->daily();
        $schedule->command('auto:disable_inactive_users')->daily();
        $schedule->command('auto:softdelete_disabled_users')->daily();
        $schedule->command('auto:recycle_claimed_torrent_requests')->daily();
        $schedule->command('auto:correct_history')->daily();
        $schedule->command('auto:sync_peers')->daily();
        $schedule->command('auto:email-blacklist-update')->weekends();
        //$schedule->command('auto:ban_disposable_users')->weekends();
        //$schedule->command('backup:clean')->daily();
        //$schedule->command('backup:run')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
