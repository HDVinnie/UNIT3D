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

namespace App\Http\Controllers\Staff;

use App\Helpers\SystemInformation;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SslCertificate\SslCertificate;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\HomeControllerTest
 */
class HomeController extends Controller
{
    /**
     * Display Staff Dashboard.
     *
     *
     * @throws \Exception
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        // User Info
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $users = DB::table('users')
            ->selectRaw('count(*) as total')
            ->selectRaw(\sprintf('count(case when group_id = %s then 1 end) as banned', $bannedGroup[0]))
            ->selectRaw(\sprintf('count(case when group_id = %s then 1 end) as validating', $validatingGroup[0]))
            ->first();

        // Torrent Info
        $torrents = DB::table('torrents')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when status = 0 then 1 end) as pending')
            ->selectRaw('count(case when status = 2 then 1 end) as rejected')
            ->selectRaw('count(case when status = 3 then 1 end) as postponed')
            ->first();

        // Peers Info
        $peers = DB::table('peers')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when seeder = 0 then 1 end) as leechers')
            ->selectRaw('count(case when seeder = 1 then 1 end) as seeders')
            ->first();

        // Reports Info
        $reports = DB::table('reports')
            ->selectRaw('count(case when solved = 0 then 1 end) as unsolved')
            ->first();

        // Pending Applications Count
        $apps = DB::table('applications')
            ->selectRaw('count(case when status = 0 then 1 end) as pending')
            ->first();

        // SSL Info
        try {
            $certificate = $request->secure() ? SslCertificate::createForHostName(\config('app.url')) : '';
        } catch (\Exception) {
            $certificate = '';
        }

        // System Information
        $systemInformation = new SystemInformation();
        $uptime = $systemInformation->uptime();
        $ram = $systemInformation->memory();
        $disk = $systemInformation->disk();
        $avg = $systemInformation->avg();
        $basic = $systemInformation->basic();

        // Directory Permissions
        $filePermissions = $systemInformation->directoryPermissions();

        return \view('Staff.dashboard.index', [
            'users'              => $users,
            'torrents'           => $torrents,
            'peers'              => $peers,
            'reports'            => $reports,
            'apps'               => $apps,
            'certificate'        => $certificate,
            'uptime'             => $uptime,
            'ram'                => $ram,
            'disk'               => $disk,
            'avg'                => $avg,
            'basic'              => $basic,
            'file_permissions'   => $filePermissions,
        ]);
    }
}
