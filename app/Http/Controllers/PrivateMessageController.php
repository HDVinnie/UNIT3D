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

use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\PrivateMessageControllerTest
 */
class PrivateMessageController extends Controller
{
    /**
     * Search PM Inbox.
     */
    public function searchPMInbox(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $pms = PrivateMessage::where('receiver_id', '=', $user->id)->where([
            ['subject', 'like', '%'.$request->input('subject').'%'],
        ])->latest()->paginate(20);

        return \view('pm.inbox', ['pms' => $pms, 'user' => $user]);
    }

    /**
     * Search PM Outbox.
     */
    public function searchPMOutbox(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $pms = PrivateMessage::where('sender_id', '=', $user->id)->where([
            ['subject', 'like', '%'.$request->input('subject').'%'],
        ])->latest()->paginate(20);

        return \view('pm.outbox', ['pms' => $pms, 'user' => $user]);
    }

    /**
     * View Inbox.
     */
    public function getPrivateMessages(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $pms = PrivateMessage::where('receiver_id', '=', $user->id)->latest()->paginate(25);

        return \view('pm.inbox', ['pms' => $pms, 'user' => $user]);
    }

    /**
     * View Outbox.
     */
    public function getPrivateMessagesSent(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $pms = PrivateMessage::where('sender_id', '=', $user->id)->latest()->paginate(20);

        return \view('pm.outbox', ['pms' => $pms, 'user' => $user]);
    }

    /**
     * View A Message.
     */
    public function getPrivateMessageById(Request $request, PrivateMessage $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $pm = PrivateMessage::where('id', '=', $id)->firstOrFail();

        if ($pm->sender_id == $user->id || $pm->receiver_id == $user->id) {
            if ($user->id === $pm->receiver_id && $pm->read === 0) {
                $pm->read = 1;
                $pm->save();
            }

            return \view('pm.message', ['pm' => $pm, 'user' => $user]);
        }

        return \redirect()->route('inbox')
            ->withErrors('What Are You Trying To Do Here!');
    }

    /**
     * Create Message Form.
     */
    public function makePrivateMessage(Request $request, string $receiverId = '', string $username = ''): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();

        return \view('pm.send', ['user' => $user, 'receiver_id' => $receiverId, 'username' => $username]);
    }

    /**
     * Create A Message.
     */
    public function sendPrivateMessage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $dest = 'default';
        if ($request->has('dest') && $request->input('dest') === 'profile') {
            $dest = 'profile';
        }

        if ($request->has('receiver_id')) {
            $recipient = User::where('username', '=', $request->input('receiver_id'))->firstOrFail();
        } else {
            return \redirect()->route('create', ['username' => $request->user()->username, 'id' => $request->user()->id])
                ->withErrors($v->errors());
        }

        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = $user->id;
        $privateMessage->receiver_id = $recipient->id;
        $privateMessage->subject = $request->input('subject');
        $privateMessage->message = $request->input('message');
        $privateMessage->read = 0;

        $v = \validator($privateMessage->toArray(), [
            'sender_id'   => 'required',
            'receiver_id' => 'required',
            'subject'     => 'required',
            'message'     => 'required',
            'read'        => 'required',
        ]);

        if ($v->fails()) {
            if ($dest === 'profile') {
                return \redirect()->route('users.show', ['username' => $recipient->username])
                    ->withErrors($v->errors());
            }

            return \redirect()->route('create', ['username' => $request->user()->username, 'id' => $request->user()->id])
                ->withErrors($v->errors());
        }
        $privateMessage->save();
        if ($dest === 'profile') {
            return \redirect()->route('users.show', ['username' => $recipient->username])
                ->withSuccess('Your PM Was Sent Successfully!');
        }

        return \redirect()->route('inbox')
            ->withSuccess('Your PM Was Sent Successfully!');
    }

    /**
     * Reply To A Message.
     */
    public function replyPrivateMessage(Request $request, PrivateMessage $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $message = PrivateMessage::where('id', '=', $id)->firstOrFail();

        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = $user->id;
        $privateMessage->receiver_id = $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
        $privateMessage->subject = $message->subject;
        $privateMessage->message = $request->input('message');
        $privateMessage->related_to = $message->id;
        $privateMessage->read = 0;

        $v = \validator($privateMessage->toArray(), [
            'sender_id'   => 'required',
            'receiver_id' => 'required',
            'subject'     => 'required',
            'message'     => 'required',
            'related_to'  => 'required',
            'read'        => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('inbox')
                ->withErrors($v->errors());
        }
        $privateMessage->save();

        return \redirect()->route('inbox')
            ->withSuccess('Your PM Was Sent Successfully!');
    }

    /**
     * Delete A Message.
     *
     *
     *@throws \Exception
     */
    public function deletePrivateMessage(Request $request, PrivateMessage $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $pm = PrivateMessage::where('id', '=', $id)->firstOrFail();

        $dest = 'default';
        if ($request->has('dest') && $request->input('dest') === 'outbox') {
            $dest = 'outbox';
        }

        if ($pm->sender_id == $user->id || $pm->receiver_id == $user->id) {
            $pm->delete();

            if ($dest === 'outbox') {
                return \redirect()->route('outbox')->withSuccess('PM Was Deleted Successfully!');
            }

            return \redirect()->route('inbox')
                ->withSuccess('PM Was Deleted Successfully!');
        }

        return \redirect()->route('inbox')
                ->withErrors('What Are You Trying To Do Here!');
    }

    /**
     * Empty Private Message Inbox.
     */
    public function emptyInbox(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        PrivateMessage::where('receiver_id', '=', $user->id)->delete();

        return \redirect()->route('inbox')
                ->withSuccess('PM Was Deleted Successfully!');
    }

    /**
     * Mark All Messages As Read.
     */
    public function markAllAsRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        foreach (PrivateMessage::where('receiver_id', '=', $user->id)->get() as $pm) {
            $pm->read = 1;
            $pm->save();
        }

        return \redirect()->route('inbox')
            ->withSuccess('Your Messages Have All Been Marked As Read!');
    }
}
