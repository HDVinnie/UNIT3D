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

use App\Jobs\SendDisableUserMail;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoDisableInactiveUsersTest
 */
class AutoDisableInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:disable_inactive_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User account must be at least x days old & user account x days Of inactivity to be disabled';

    /**
     * Execute the console command.
     *
     *@throws \Exception
     *
     * @return void
     */
    public function handle(): void
    {
        if (\config('pruning.user_pruning') == true) {
            $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));

            $current = Carbon::now();

            $matches = User::whereIn('group_id', \config('pruning.group_ids'))->get();

            $users = $matches->where('created_at', '<', $current->copy()->subDays(\config('pruning.account_age'))->toDateTimeString())
                ->where('last_login', '<', $current->copy()->subDays(\config('pruning.last_login'))->toDateTimeString())
                ->all();

            foreach ($users as $user) {
                if ($user->getSeeding() === 0) {
                    $user->group_id = $disabledGroup[0];
                    $user->can_upload = 0;
                    $user->can_download = 0;
                    $user->can_comment = 0;
                    $user->can_invite = 0;
                    $user->can_request = 0;
                    $user->can_chat = 0;
                    $user->disabled_at = Carbon::now();
                    $user->save();

                    // Send Email
                    \dispatch(new SendDisableUserMail($user));
                }
            }
        }
        $this->comment('Automated User Disable Command Complete');
    }
}
