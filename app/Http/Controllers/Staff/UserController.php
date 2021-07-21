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

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Group;
use App\Models\Invite;
use App\Models\Like;
use App\Models\Message;
use App\Models\Note;
use App\Models\Peer;
use App\Models\Post;
use App\Models\PrivateMessage;
use App\Models\Thank;
use App\Models\Topic;
use App\Models\Torrent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\UserControllerTest
 */
class UserController extends Controller
{
    /**
     * @var string[]
     */
    private const WEIGHTS = [
        'is_modo',
        'is_admin',
    ];

    /**
     * Users List.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.user.index');
    }

    /**
     * User Edit Form.
     */
    public function settings(User $username): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $groups = Group::all();
        $notes = Note::where('user_id', '=', $user->id)->latest()->paginate(25);

        return \view('Staff.user.edit', [
            'user'   => $user,
            'groups' => $groups,
            'notes'  => $notes,
        ]);
    }

    /**
     * Edit A User.
     */
    public function edit(Request $request, User $username): \Illuminate\Http\RedirectResponse
    {
        $user = User::with('group')->where('username', '=', $username)->firstOrFail();
        $staff = $request->user();

        $sendto = (int) $request->input('group_id');

        $sender = -1;
        $target = -1;
        foreach (self::WEIGHTS as $pos => $weight) {
            if ($user->group->$weight && $user->group->$weight == 1) {
                $target = $pos;
            }
            if ($staff->group->$weight && $staff->group->$weight == 1) {
                $sender = $pos;
            }
        }

        if ($target == 1 && $user->group->id == 10) {
            $target = 2;
        }
        if ($sender == 1 && $staff->group->id == 10) {
            $sender = 2;
        }

        // Hard coded until group change.

        if ($target >= $sender || ($sender == 0 && ($sendto === 6 || $sendto === 4 || $sendto === 10)) || ($sender == 1 && ($sendto === 4 || $sendto === 10))) {
            return \redirect()->route('users.show', ['username' => $user->username])
                ->withErrors('You Are Not Authorized To Perform This Action!');
        }

        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->uploaded = $request->input('uploaded');
        $user->downloaded = $request->input('downloaded');
        $user->title = $request->input('title');
        $user->about = $request->input('about');
        $user->group_id = (int) $request->input('group_id');
        $user->save();

        return \redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('Account Was Updated Successfully!');
    }

    /**
     * Edit A Users Permissions.
     */
    public function permissions(Request $request, User $username): \Illuminate\Http\RedirectResponse
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $user->can_upload = $request->input('can_upload');
        $user->can_download = $request->input('can_download');
        $user->can_comment = $request->input('can_comment');
        $user->can_invite = $request->input('can_invite');
        $user->can_request = $request->input('can_request');
        $user->can_chat = $request->input('can_chat');
        $user->save();

        return \redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('Account Permissions Successfully Edited');
    }

    /**
     * Edit A Users Password.
     */
    protected function password(Request $request, User $username): \Illuminate\Http\RedirectResponse
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return \redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('Account Password Was Updated Successfully!');
    }

    /**
     * Delete A User.
     *
     * @param \App\Models\User $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function destroy(User $username): \Illuminate\Http\RedirectResponse
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        \abort_if($user->group->is_modo || \auth()->user()->id == $user->id, 403);

        // Removes UserID from Torrents if any and replaces with System UserID (1)
        foreach (Torrent::withAnyStatus()->where('user_id', '=', $user->id)->get() as $tor) {
            $tor->user_id = 1;
            $tor->save();
        }
        // Removes UserID from Comments if any and replaces with System UserID (1)
        foreach (Comment::where('user_id', '=', $user->id)->get() as $com) {
            $com->user_id = 1;
            $com->save();
        }
        // Removes UserID from Posts if any and replaces with System UserID (1)
        foreach (Post::where('user_id', '=', $user->id)->get() as $post) {
            $post->user_id = 1;
            $post->save();
        }
        // Removes UserID from Topic Creators if any and replaces with System UserID (1)
        foreach (Topic::where('first_post_user_id', '=', $user->id)->get() as $topic) {
            $topic->first_post_user_id = 1;
            $topic->save();
        }
        // Removes UserID from Topic if any and replaces with System UserID (1)
        foreach (Topic::where('last_post_user_id', '=', $user->id)->get() as $topic) {
            $topic->last_post_user_id = 1;
            $topic->save();
        }
        // Removes UserID from PM if any and replaces with System UserID (1)
        foreach (PrivateMessage::where('sender_id', '=', $user->id)->get() as $sent) {
            $sent->sender_id = 1;
            $sent->save();
        }
        // Removes UserID from PM if any and replaces with System UserID (1)
        foreach (PrivateMessage::where('receiver_id', '=', $user->id)->get() as $received) {
            $received->receiver_id = 1;
            $received->save();
        }
        // Removes all Posts made by User from the shoutbox
        foreach (Message::where('user_id', '=', $user->id)->get() as $shout) {
            $shout->delete();
        }
        // Removes all notes for user
        foreach (Note::where('user_id', '=', $user->id)->get() as $note) {
            $note->delete();
        }
        // Removes all likes for user
        foreach (Like::where('user_id', '=', $user->id)->get() as $like) {
            $like->delete();
        }
        // Removes all thanks for user
        foreach (Thank::where('user_id', '=', $user->id)->get() as $thank) {
            $thank->delete();
        }
        // Removes all follows for user
        foreach (Follow::where('user_id', '=', $user->id)->get() as $follow) {
            $follow->delete();
        }
        // Removes UserID from Sent Invites if any and replaces with System UserID (1)
        foreach (Invite::where('user_id', '=', $user->id)->get() as $sentInvite) {
            $sentInvite->user_id = 1;
            $sentInvite->save();
        }
        // Removes UserID from Received Invite if any and replaces with System UserID (1)
        foreach (Invite::where('accepted_by', '=', $user->id)->get() as $receivedInvite) {
            $receivedInvite->accepted_by = 1;
            $receivedInvite->save();
        }
        // Removes all Peers for user
        foreach (Peer::where('user_id', '=', $user->id)->get() as $peer) {
            $peer->delete();
        }

        if ($user->delete()) {
            return \redirect()->route('staff.dashboard.index')
                ->withSuccess('Account Has Been Removed');
        }

        return \redirect()->route('staff.dashboard.index')
            ->withErrors('Something Went Wrong!');
    }
}
