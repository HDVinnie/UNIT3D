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
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Unit\Console\Commands\AutoBonAllocationTest
 */
class AutoBonAllocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:bon_allocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allocates Bonus Points To Users Based On Peer Activity.';

    /**
     * Execute the console command.
     *
     *
     * @return void
     */
    public function handle(ByteUnits $byteUnits): void
    {
        $dyingTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('torrents.seeders', 1)
            ->where('torrents.times_completed', '>', 2)
            ->where('peers.seeder', 1)
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $legendaryTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->whereRaw('torrents.created_at < date_sub(now(), interval 12 month)')
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $oldTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->whereRaw('torrents.created_at < date_sub(now(), Interval 6 month)')
            ->whereRaw('torrents.created_at > date_sub(now(), interval 12 month)')
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $hugeTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $byteUnits->bytesFromUnit('100GiB'))
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $largeTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $byteUnits->bytesFromUnit('25GiB'))
            ->where('torrents.size', '<', $byteUnits->bytesFromUnit('100GiB'))
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $regularTorrent = DB::table('peers')
            ->select(DB::raw('count(DISTINCT(peers.info_hash)) as value'), 'peers.user_id')
            ->join('torrents', 'torrents.id', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $byteUnits->bytesFromUnit('1GiB'))
            ->where('torrents.size', '<', $byteUnits->bytesFromUnit('25GiB'))
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->groupBy('peers.user_id')
            ->get()
            ->toArray();

        $participaintSeeder = DB::table('history')
            ->select(DB::raw('count(DISTINCT(history.info_hash)) as value'), 'history.user_id')
            ->join('torrents', 'torrents.info_hash', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000)
            ->where('history.seedtime', '<', 2_592_000 * 2)
            ->groupBy('history.user_id')
            ->get()
            ->toArray();

        $teamplayerSeeder = DB::table('history')
            ->select(DB::raw('count(DISTINCT(history.info_hash)) as value'), 'history.user_id')
            ->join('torrents', 'torrents.info_hash', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 2)
            ->where('history.seedtime', '<', 2_592_000 * 3)
            ->groupBy('history.user_id')
            ->get()
            ->toArray();

        $commitedSeeder = DB::table('history')
            ->select(DB::raw('count(DISTINCT(history.info_hash)) as value'), 'history.user_id')
            ->join('torrents', 'torrents.info_hash', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 3)
            ->where('history.seedtime', '<', 2_592_000 * 6)
            ->groupBy('history.user_id')
            ->get()
            ->toArray();

        $mvpSeeder = DB::table('history')
            ->select(DB::raw('count(DISTINCT(history.info_hash)) as value'), 'history.user_id')
            ->join('torrents', 'torrents.info_hash', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 6)
            ->where('history.seedtime', '<', 2_592_000 * 12)
            ->groupBy('history.user_id')
            ->get()
            ->toArray();

        $legendarySeeder = DB::table('history')
            ->select(DB::raw('count(DISTINCT(history.info_hash)) as value'), 'history.user_id')
            ->join('torrents', 'torrents.info_hash', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 12)
            ->groupBy('history.user_id')
            ->get()
            ->toArray();

        //Move data from SQL to array

        $array = [];

        foreach ($dyingTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 2;
            } else {
                $array[$value->user_id] = $value->value * 2;
            }
        }

        foreach ($legendaryTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 1.5;
            } else {
                $array[$value->user_id] = $value->value * 1.5;
            }
        }

        foreach ($oldTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 1;
            } else {
                $array[$value->user_id] = $value->value * 1;
            }
        }

        foreach ($hugeTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.75;
            } else {
                $array[$value->user_id] = $value->value * 0.75;
            }
        }

        foreach ($largeTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.50;
            } else {
                $array[$value->user_id] = $value->value * 0.50;
            }
        }

        foreach ($regularTorrent as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.25;
            } else {
                $array[$value->user_id] = $value->value * 0.25;
            }
        }

        foreach ($participaintSeeder as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.25;
            } else {
                $array[$value->user_id] = $value->value * 0.25;
            }
        }

        foreach ($teamplayerSeeder as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.50;
            } else {
                $array[$value->user_id] = $value->value * 0.50;
            }
        }

        foreach ($commitedSeeder as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 0.75;
            } else {
                $array[$value->user_id] = $value->value * 0.75;
            }
        }

        foreach ($mvpSeeder as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 1;
            } else {
                $array[$value->user_id] = $value->value * 1;
            }
        }

        foreach ($legendarySeeder as $key => $value) {
            if (\array_key_exists($value->user_id, $array)) {
                $array[$value->user_id] += $value->value * 2;
            } else {
                $array[$value->user_id] = $value->value * 2;
            }
        }

        //Move data from array to BonTransactions table
        /*foreach ($array as $key => $value) {
            $log = new BonTransactions();
            $log->itemID = 0;
            $log->name = "Seeding Award";
            $log->cost = $value;
            $log->receiver = $key;
            $log->comment = "Seeding Award";
            $log->save();
        }*/

        //Move data from array to Users table
        foreach ($array as $key => $value) {
            $user = User::where('id', '=', $key)->first();
            $user->seedbonus += $value;
            $user->save();
        }
        $this->comment('Automated BON Allocation Command Complete');
    }
}
