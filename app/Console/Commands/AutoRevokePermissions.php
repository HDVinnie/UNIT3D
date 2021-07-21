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

use App\Models\Group;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Todo\Unit\Console\Commands\AutoRevokePermissionsTest
 */
class AutoRevokePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:revoke_permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revokes Certain Permissions Of Users Who Have Above X Active Warnings';

    /**
     * Execute the console command.
     *
     *@throws \Exception
     *
     * @return void
     */
    public function handle(): void
    {
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $leechGroup = \cache()->rememberForever('leech_group', fn () => Group::where('slug', '=', 'leech')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));
        $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

        User::whereNotIn('group_id', [$bannedGroup[0], $validatingGroup[0], $leechGroup[0], $disabledGroup[0], $prunedGroup[0]])->update(['can_download' => '1', 'can_request' => '1']);
        User::whereIn('group_id', [$bannedGroup[0], $validatingGroup[0], $leechGroup[0], $disabledGroup[0], $prunedGroup[0]])->update(['can_download' => '0', 'can_request' => '0']);

        $warning = Warning::with('warneduser')->select(DB::raw('user_id, count(*) as value'))->where('active', '=', 1)->groupBy('user_id')->having('value', '>=', \config('hitrun.revoke'))->get();

        foreach ($warning as $deny) {
            if ($deny->warneduser->can_download == 1 && $deny->warneduser->can_request == 1) {
                $deny->warneduser->can_download = 0;
                $deny->warneduser->can_request = 0;
                $deny->warneduser->save();
            }
        }
        $this->comment('Automated User Permissions Revoke Command Complete');
    }
}
