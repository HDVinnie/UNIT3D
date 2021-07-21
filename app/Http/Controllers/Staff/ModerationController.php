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

use App\Helpers\TorrentHelper;
use App\Http\Controllers\Controller;
use App\Models\PrivateMessage;
use App\Models\Torrent;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\ModerationControllerTest
 */
class ModerationController extends Controller
{
    /**
     * ModerationController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Torrent Moderation Panel.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $current = Carbon::now();
        $pending = Torrent::with(['user', 'category', 'type'])->pending()->get();
        $postponed = Torrent::with(['user', 'category', 'type'])->postponed()->get();
        $rejected = Torrent::with(['user', 'category', 'type'])->rejected()->get();

        return \view('Staff.moderation.index', [
            'current'   => $current,
            'pending'   => $pending,
            'postponed' => $postponed,
            'rejected'  => $rejected,
        ]);
    }

    /**
     * Approve A Torrent.
     *
     * @param \App\Models\Torrent $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Torrent $id): \Illuminate\Http\RedirectResponse
    {
        $torrent = Torrent::withAnyStatus()->where('id', '=', $id)->first();

        if ($torrent->status !== 1) {
            $appurl = \config('app.url');
            $user = $torrent->user;
            $username = $user->username;
            $anon = $torrent->anon;

            // Announce To Shoutbox
            if ($anon == 0) {
                $this->chatRepository->systemMessage(
                    \sprintf('User [url=%s/users/', $appurl).$username.']'.$username.\sprintf('[/url] has uploaded [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url] grab it now! :slight_smile:'
                );
            } else {
                $this->chatRepository->systemMessage(
                    \sprintf('An anonymous user has uploaded [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url] grab it now! :slight_smile:'
                );
            }

            TorrentHelper::approveHelper($torrent->id);

            return \redirect()->route('staff.moderation.index')
                ->withSuccess('Torrent Approved');
        }

        return \redirect()->route('staff.moderation.index')
            ->withErrors('Torrent Already Approved');
    }

    /**
     * Postpone A Torrent.
     */
    public function postpone(Request $request): \Illuminate\Http\RedirectResponse
    {
        $v = \validator($request->all(), [
            'id'      => 'required|exists:torrents',
            'slug'    => 'required|exists:torrents',
            'message' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.moderation.index')
                ->withErrors($v->errors());
        }
        $user = $request->user();
        $torrent = Torrent::withAnyStatus()->where('id', '=', $request->input('id'))->first();
        $torrent->markPostponed();
        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = $user->id;
        $privateMessage->receiver_id = $torrent->user_id;
        $privateMessage->subject = \sprintf('Your upload, %s ,has been postponed by %s', $torrent->name, $user->username);
        $privateMessage->message = \sprintf('Greetings, 

 Your upload, %s ,has been postponed. Please see below the message from the staff member. 

%s', $torrent->name, $request->input('message'));
        $privateMessage->save();

        return \redirect()->route('staff.moderation.index')
            ->withSuccess('Torrent Postponed');
    }

    /**
     * Reject A Torrent.
     */
    public function reject(Request $request): \Illuminate\Http\RedirectResponse
    {
        $v = \validator($request->all(), [
            'id'      => 'required|exists:torrents',
            'slug'    => 'required|exists:torrents',
            'message' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.moderation.index')
                ->withErrors($v->errors());
        }
        $user = $request->user();
        $torrent = Torrent::withAnyStatus()->where('id', '=', $request->input('id'))->first();
        $torrent->markRejected();
        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = $user->id;
        $privateMessage->receiver_id = $torrent->user_id;
        $privateMessage->subject = \sprintf('Your upload, %s ,has been rejected by %s', $torrent->name, $user->username);
        $privateMessage->message = \sprintf('Greetings, 

 Your upload %s has been rejected. Please see below the message from the staff member. 

%s', $torrent->name, $request->input('message'));
        $privateMessage->save();

        return \redirect()->route('staff.moderation.index')
            ->withSuccess('Torrent Rejected');
    }
}
