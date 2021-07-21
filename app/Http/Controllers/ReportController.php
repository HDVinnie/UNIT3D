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

use App\Models\Report;
use App\Models\Torrent;
use App\Models\TorrentRequest;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\ReportControllerTest
 */
class ReportController extends Controller
{
    /**
     * ReportController Constructor.
     */
    public function __construct(private Report $report)
    {
    }

    /**
     * Create A Request Report.
     */
    public function request(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $torrentRequest = TorrentRequest::findOrFail($id);
        $reportedBy = $request->user();
        $reportedUser = $torrentRequest->user;

        $v = \validator($request->all(), [
            'message' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('request', ['id' => $id])
                ->withErrors($v->errors());
        }
        $this->report->create([
            'type'          => 'Request',
            'request_id'    => $torrentRequest->id,
            'torrent_id'    => 0,
            'reporter_id'   => $reportedBy->id,
            'reported_user' => $reportedUser->id,
            'title'         => $torrentRequest->name,
            'message'       => $request->get('message'),
            'solved'        => 0,
        ]);

        return \redirect()->route('request', ['id' => $id])
            ->withSuccess('Your report has been successfully sent');
    }

    /**
     * Create A Torrent Report.
     */
    public function torrent(Request $request, Torrent $id): \Illuminate\Http\RedirectResponse
    {
        $torrent = Torrent::findOrFail($id);
        $reportedBy = $request->user();
        $reportedUser = $torrent->user;

        $v = \validator($request->all(), [
            'message' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('torrent', ['id' => $id])
                ->withErrors($v->errors());
        }
        $this->report->create([
            'type'          => 'Torrent',
            'torrent_id'    => $torrent->id,
            'request_id'    => 0,
            'reporter_id'   => $reportedBy->id,
            'reported_user' => $reportedUser->id,
            'title'         => $torrent->name,
            'message'       => $request->get('message'),
            'solved'        => 0,
        ]);

        return \redirect()->route('torrent', ['id' => $id])
            ->withSuccess('Your report has been successfully sent');
    }

    /**
     * Create A User Report.
     */
    public function user(Request $request, User $username): \Illuminate\Http\RedirectResponse
    {
        $reportedUser = User::where('username', '=', $username)->firstOrFail();
        $reportedBy = $request->user();

        $v = \validator($request->all(), [
            'message' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('users.show', ['username' => $username])
                ->withErrors($v->errors());
        }
        $this->report->create([
            'type'          => 'User',
            'torrent_id'    => 0,
            'request_id'    => 0,
            'reporter_id'   => $reportedBy->id,
            'reported_user' => $reportedUser->id,
            'title'         => $reportedUser->username,
            'message'       => $request->get('message'),
            'solved'        => 0,
        ]);

        return \redirect()->route('users.show', ['username' => $username])
            ->withSuccess('Your report has been successfully sent');
    }
}
