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

use App\Helpers\ByteUnits;
use App\Models\Group;
use App\Models\History;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoGroupTest
 */
class AutoGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Change A Users Group Class If Requirements Met';

    /**
     * Execute the console command.
     *
     *
     * @return void
     */
    public function handle(ByteUnits $byteUnits): void
    {
        // Temp Hard Coding of Immune Groups (Config Files To Come)
        $current = Carbon::now();
        $groups = Group::select(['id'])->where('autogroup', '=', 1)->get()->toArray();
        foreach (User::whereIn('group_id', $groups)->get() as $user) {
            $hiscount = History::where('user_id', '=', $user->id)->count();

            // Temp Hard Coding of Group Requirements (Config Files To Come) (Upload in Bytes!) (Seedtime in Seconds!)

            // Leech ratio dropped below sites minimum
            if ($user->getRatio() < \config('other.ratio') && $user->group_id != 15) {
                $user->group_id = 15;
                $user->can_request = 0;
                $user->can_invite = 0;
                $user->can_download = 0;
                $user->save();
            }
            // User >= 0 and ratio above sites minimum
            if ($user->uploaded >= 0 && $user->getRatio() >= \config('other.ratio') && $user->group_id != 3) {
                $user->group_id = 3;
                $user->can_request = 1;
                $user->can_invite = 1;
                $user->can_download = 1;
                $user->save();
            }

            // PowerUser >= 1TiB and account 1 month old
            if ($user->uploaded >= $byteUnits->bytesFromUnit('1TiB') && $user->getRatio() >= \config('other.ratio') && $user->created_at < $current->copy()->subDays(30)->toDateTimeString() && $user->group_id != 11) {
                $user->group_id = 11;
                $user->save();
            }
            // SuperUser >= 5TiB and account 2 month old
            if ($user->uploaded >= $byteUnits->bytesFromUnit('5TiB') && $user->getRatio() >= \config('other.ratio') && $user->created_at < $current->copy()->subDays(60)->toDateTimeString() && $user->group_id != 12) {
                $user->group_id = 12;
                $user->save();
            }
            // ExtremeUser >= 20TiB and account 3 month old
            if ($user->uploaded >= $byteUnits->bytesFromUnit('20TiB') && $user->getRatio() >= \config('other.ratio') && $user->created_at < $current->copy()->subDays(90)->toDateTimeString() && $user->group_id != 13) {
                $user->group_id = 13;
                $user->save();
            }
            // InsaneUser >= 50TiB and account 6 month old
            if ($user->uploaded >= $byteUnits->bytesFromUnit('50TiB') && $user->getRatio() >= \config('other.ratio') && $user->created_at < $current->copy()->subDays(180)->toDateTimeString() && $user->group_id != 14) {
                $user->group_id = 14;
                $user->save();
            }
            // Seeder Seedsize >= 5TiB and account 1 month old and seedtime average 30 days or better
            if ($user->getTotalSeedSize() >= $byteUnits->bytesFromUnit('5TiB') && $user->getRatio() >= \config('other.ratio') && \round($user->getTotalSeedTime() / \max(1, $hiscount)) > 2_592_000 && $user->created_at < $current->copy()->subDays(30)->toDateTimeString() && $user->group_id != 17) {
                $user->group_id = 17;
                $user->save();
            }
            // Veteran >= 100TiB and account 1 year old
            if ($user->uploaded >= $byteUnits->bytesFromUnit('100TiB') && $user->getRatio() >= \config('other.ratio') && $user->created_at < $current->copy()->subDays(365)->toDateTimeString() && $user->group_id != 16) {
                $user->group_id = 16;
                $user->save();
            }
            // Archivist Seedsize >= 10TiB and account 3 month old and seedtime average 60 days or better
            if ($user->getTotalSeedSize() >= $byteUnits->bytesFromUnit('10TiB') && $user->getRatio() >= \config('other.ratio') && \round($user->getTotalSeedTime() / \max(1, $hiscount)) > 2_592_000 * 2 && $user->created_at < $current->copy()->subDays(90)->toDateTimeString() && $user->group_id != 18) {
                $user->group_id = 18;
                $user->save();
            }
        }
        $this->comment('Automated User Group Command Complete');
    }
}
