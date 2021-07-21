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

use App\Jobs\SendDeleteUserMail;
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
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @see \Tests\Unit\Console\Commands\AutoSoftDeleteDisabledUsersTest
 */
class AutoSoftDeleteDisabledUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:softdelete_disabled_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User account must be In disabled group for at least x days';

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
            $prunedGroup = \cache()->rememberForever('pruned_group', fn () => Group::where('slug', '=', 'pruned')->pluck('id'));

            $current = Carbon::now();
            $users = User::where('group_id', '=', $disabledGroup[0])
                ->where('disabled_at', '<', $current->copy()->subDays(\config('pruning.soft_delete'))->toDateTimeString())
                ->get();

            foreach ($users as $user) {
                // Send Email
                \dispatch(new SendDeleteUserMail($user));

                $user->can_upload = 0;
                $user->can_download = 0;
                $user->can_comment = 0;
                $user->can_invite = 0;
                $user->can_request = 0;
                $user->can_chat = 0;
                $user->group_id = $prunedGroup[0];
                $user->deleted_by = 1;
                $user->save();

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

                $user->delete();
            }
        }
        $this->comment('Automated Soft Delete Disabled Users Command Complete');
    }
}
