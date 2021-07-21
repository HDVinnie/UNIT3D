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

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use App\Models\History;
use App\Models\Language;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\TorrentRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\StatsControllerTest
 */
class StatsController extends Controller
{
    /**
     * @var \Carbon\Carbon|mixed
     */
    public mixed $carbon;

    /**
     * StatsController Constructor.
     */
    public function __construct()
    {
        $this->carbon = Carbon::now()->addMinutes(10);
    }

    /**
     * Show Extra-Stats Index.
     *
     * @throws \Exception
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Total Members Count (All Groups)
        $allUser = \cache()->remember('all_user', $this->carbon, fn () => User::withTrashed()->count());

        // Total Active Members Count (Not Validating, Banned, Disabled, Pruned)
        $activeUser = \cache()->remember('active_user', $this->carbon, function () {
            $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
            $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
            $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));
            $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

            return User::whereNotIn('group_id', [$validatingGroup[0], $bannedGroup[0], $disabledGroup[0], $prunedGroup[0]])->count();
        });

        // Total Disabled Members Count
        $disabledUser = \cache()->remember('disabled_user', $this->carbon, function () {
            $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));

            return User::where('group_id', '=', $disabledGroup[0])->count();
        });

        // Total Pruned Members Count
        $prunedUser = \cache()->remember('pruned_user', $this->carbon, function () {
            $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

            return User::onlyTrashed()->where('group_id', '=', $prunedGroup[0])->count();
        });

        // Total Banned Members Count
        $bannedUser = \cache()->remember('banned_user', $this->carbon, function () {
            $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));

            return User::where('group_id', '=', $bannedGroup[0])->count();
        });

        // Total Torrents Count
        $numTorrent = \cache()->remember('num_torrent', $this->carbon, fn () => Torrent::count());

        // Total Categories With Torrent Count
        $categories = Category::withCount('torrents')->get()->sortBy('position');

        // Total HD Count
        $numHd = \cache()->remember('num_hd', $this->carbon, fn () => Torrent::where('sd', '=', 0)->count());

        // Total SD Count
        $numSd = \cache()->remember('num_sd', $this->carbon, fn () => Torrent::where('sd', '=', 1)->count());

        // Total Torrent Size
        $torrentSize = \cache()->remember('torrent_size', $this->carbon, fn () => Torrent::sum('size'));

        // Total Seeders
        $numSeeders = \cache()->remember('num_seeders', $this->carbon, fn () => Peer::where('seeder', '=', 1)->count());

        // Total Leechers
        $numLeechers = \cache()->remember('num_leechers', $this->carbon, fn () => Peer::where('seeder', '=', 0)->count());

        // Total Peers
        $numPeers = \cache()->remember('num_peers', $this->carbon, fn () => Peer::count());

        //Total Upload Traffic Without Double Upload
        $actualUpload = \cache()->remember('actual_upload', $this->carbon, fn () => History::sum('actual_uploaded'));

        //Total Upload Traffic With Double Upload
        $creditedUpload = \cache()->remember('credited_upload', $this->carbon, fn () => History::sum('uploaded'));

        //Total Download Traffic Without Freeleech
        $actualDownload = \cache()->remember('actual_download', $this->carbon, fn () => History::sum('actual_downloaded'));

        //Total Download Traffic With Freeleech
        $creditedDownload = \cache()->remember('credited_download', $this->carbon, fn () => History::sum('downloaded'));

        //Total Up/Down Traffic without perks
        $actualUpDown = $actualUpload + $actualDownload;

        //Total Up/Down Traffic with perks
        $creditedUpDown = $creditedUpload + $creditedDownload;

        return \view('stats.index', [
            'all_user'          => $allUser,
            'active_user'       => $activeUser,
            'disabled_user'     => $disabledUser,
            'pruned_user'       => $prunedUser,
            'banned_user'       => $bannedUser,
            'num_torrent'       => $numTorrent,
            'categories'        => $categories,
            'num_hd'            => $numHd,
            'num_sd'            => $numSd,
            'torrent_size'      => $torrentSize,
            'num_seeders'       => $numSeeders,
            'num_leechers'      => $numLeechers,
            'num_peers'         => $numPeers,
            'actual_upload'     => $actualUpload,
            'actual_download'   => $actualDownload,
            'actual_up_down'    => $actualUpDown,
            'credited_upload'   => $creditedUpload,
            'credited_download' => $creditedDownload,
            'credited_up_down'  => $creditedUpDown,
        ]);
    }

    /**
     * Show Extra-Stats Users.
     *
     * @throws \Exception
     */
    public function uploaded(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));
        $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

        // Fetch Top Uploaders
        $uploaded = User::latest('uploaded')->whereNotIn('group_id', [$validatingGroup[0], $bannedGroup[0], $disabledGroup[0], $prunedGroup[0]])->take(100)->get();

        return \view('stats.users.uploaded', ['uploaded' => $uploaded]);
    }

    /**
     * Show Extra-Stats Users.
     *
     * @throws \Exception
     */
    public function downloaded(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));
        $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

        // Fetch Top Downloaders
        $downloaded = User::latest('downloaded')->whereNotIn('group_id', [$validatingGroup[0], $bannedGroup[0], $disabledGroup[0], $prunedGroup[0]])->take(100)->get();

        return \view('stats.users.downloaded', ['downloaded' => $downloaded]);
    }

    /**
     * Show Extra-Stats Users.
     */
    public function seeders(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Seeders
        $seeders = Peer::with('user')->select(DB::raw('user_id, count(*) as value'))->where('seeder', '=', 1)->groupBy('user_id')->latest('value')->take(100)->get();

        return \view('stats.users.seeders', ['seeders' => $seeders]);
    }

    /**
     * Show Extra-Stats Users.
     */
    public function leechers(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Leechers
        $leechers = Peer::with('user')->select(DB::raw('user_id, count(*) as value'))->where('seeder', '=', 0)->groupBy('user_id')->latest('value')->take(100)->get();

        return \view('stats.users.leechers', ['leechers' => $leechers]);
    }

    /**
     * Show Extra-Stats Users.
     */
    public function uploaders(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Uploaders
        $uploaders = Torrent::with('user')->select(DB::raw('user_id, count(*) as value'))->groupBy('user_id')->latest('value')->take(100)->get();

        return \view('stats.users.uploaders', ['uploaders' => $uploaders]);
    }

    /**
     * Show Extra-Stats Users.
     *
     * @throws \Exception
     */
    public function bankers(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));
        $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

        // Fetch Top Bankers
        $bankers = User::latest('seedbonus')->whereNotIn('group_id', [$validatingGroup[0], $bannedGroup[0], $disabledGroup[0], $prunedGroup[0]])->take(100)->get();

        return \view('stats.users.bankers', ['bankers' => $bankers]);
    }

    /**
     * Show Extra-Stats Users.
     */
    public function seedtime(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Total Seedtime
        $seedtime = User::with('history')->select(DB::raw('user_id, count(*) as value'))->groupBy('user_id')->latest('value')->take(100)->sum('seedtime');

        return \view('stats.users.seedtime', ['seedtime' => $seedtime]);
    }

    /**
     * Show Extra-Stats Users.
     */
    public function seedsize(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Total Seedsize Users
        $seedsize = User::with(['peers', 'torrents'])->select(DB::raw('user_id, count(*) as value'))->groupBy('user_id')->latest('value')->take(100)->sum('size');

        return \view('stats.users.seedsize', ['seedsize' => $seedsize]);
    }

    /**
     * Show Extra-Stats Torrents.
     */
    public function seeded(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Seeded
        $seeded = Torrent::latest('seeders')->take(100)->get();

        return \view('stats.torrents.seeded', ['seeded' => $seeded]);
    }

    /**
     * Show Extra-Stats Torrents.
     */
    public function leeched(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Leeched
        $leeched = Torrent::latest('leechers')->take(100)->get();

        return \view('stats.torrents.leeched', ['leeched' => $leeched]);
    }

    /**
     * Show Extra-Stats Torrents.
     */
    public function completed(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Completed
        $completed = Torrent::latest('times_completed')->take(100)->get();

        return \view('stats.torrents.completed', ['completed' => $completed]);
    }

    /**
     * Show Extra-Stats Torrents.
     */
    public function dying(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Dying
        $dying = Torrent::where('seeders', '=', 1)->where('times_completed', '>=', '1')->latest('leechers')->take(100)->get();

        return \view('stats.torrents.dying', ['dying' => $dying]);
    }

    /**
     * Show Extra-Stats Torrents.
     */
    public function dead(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Dead
        $dead = Torrent::where('seeders', '=', 0)->latest('leechers')->take(100)->get();

        return \view('stats.torrents.dead', ['dead' => $dead]);
    }

    /**
     * Show Extra-Stats Torrent Requests.
     */
    public function bountied(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Top Bountied
        $bountied = TorrentRequest::latest('bounty')->take(100)->get();

        return \view('stats.requests.bountied', ['bountied' => $bountied]);
    }

    /**
     * Show Extra-Stats Groups.
     */
    public function groups(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Groups User Counts
        $groups = Group::oldest('position')->get();

        return \view('stats.groups.groups', ['groups' => $groups]);
    }

    /**
     * Show Extra-Stats Groups.
     */
    public function group(Group $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch Users In Group
        $group = Group::findOrFail($id);
        $users = User::withTrashed()->where('group_id', '=', $group->id)->latest()->paginate(100);

        return \view('stats.groups.group', ['users' => $users, 'group' => $group]);
    }

    /**
     * Show Extra-Stats Languages.
     */
    public function languages(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // Fetch All Languages
        $languages = Language::allowed();

        return \view('stats.languages.languages', ['languages' => $languages]);
    }
}
