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

use App\Achievements\UserMade100Posts;
use App\Achievements\UserMade200Posts;
use App\Achievements\UserMade25Posts;
use App\Achievements\UserMade300Posts;
use App\Achievements\UserMade400Posts;
use App\Achievements\UserMade500Posts;
use App\Achievements\UserMade50Posts;
use App\Achievements\UserMade600Posts;
use App\Achievements\UserMade700Posts;
use App\Achievements\UserMade800Posts;
use App\Achievements\UserMade900Posts;
use App\Achievements\UserMadeFirstPost;
use App\Models\Post;
use App\Models\Topic;
use App\Repositories\ChatRepository;
use App\Repositories\TaggedUserRepository;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\PostControllerTest
 */
class PostController extends Controller
{
    /**
     * PostController Constructor.
     */
    public function __construct(private TaggedUserRepository $taggedUserRepository, private ChatRepository $chatRepository)
    {
    }

    /**
     * Store A New Post To A Topic.
     */
    public function reply(Request $request, Topic $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $topic = Topic::findOrFail($id);
        $forum = $topic->forum;
        $category = $forum->getCategory();

        // The user has the right to create a post here?
        if (! $category->getPermission()->reply_topic || ($topic->state === 'close' && ! $request->user()->group->is_modo)) {
            return \redirect()->route('forums.index')
                ->withErrors('You Cannot Reply To This Topic!');
        }

        $post = new Post();
        $post->content = $request->input('content');
        $post->user_id = $user->id;
        $post->topic_id = $topic->id;

        $v = \validator($post->toArray(), [
            'content'  => 'required|min:1',
            'user_id'  => 'required',
            'topic_id' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('forum_topic', ['id' => $topic->id])
                ->withErrors($v->errors());
        }
        $post->save();
        $appurl = \config('app.url');
        $href = \sprintf('%s/forums/topics/%s?page=%s#post-%s', $appurl, $topic->id, $post->getPageNumber(), $post->id);
        $message = \sprintf('%s has tagged you in a forum post. You can view it [url=%s] HERE [/url]', $user->username, $href);
        if ($this->taggedUserRepository->hasTags($request->input('content'))) {
            if ($this->taggedUserRepository->contains($request->input('content'), '@here') && $user->group->is_modo) {
                $users = \collect([]);

                $topic->posts()->get()->each(function ($p) use ($users) {
                    $users->push($p->user);
                });

                $this->taggedUserRepository->messagePostUsers(
                    'forum',
                    $users,
                    $user,
                    'Staff',
                    $post
                );
            } else {
                $this->taggedUserRepository->messageTaggedPostUsers(
                    'forum',
                    $request->input('content'),
                    $user,
                    $user->username,
                    $post
                );
            }
        }
        // Save last post user data to topic table
        $topic->last_post_user_id = $user->id;
        $topic->last_post_user_username = $user->username;
        // Count post in topic
        $topic->num_post = Post::where('topic_id', '=', $topic->id)->count();
        // Update time
        $topic->last_reply_at = $post->created_at;
        // Save
        $topic->save();
        // Count posts
        $forum->num_post = $forum->getPostCount($forum->id);
        // Count topics
        $forum->num_topic = $forum->getTopicCount($forum->id);
        // Save last post user data to the forum table
        $forum->last_post_user_id = $user->id;
        $forum->last_post_user_username = $user->username;
        // Save last topic data to the forum table
        $forum->last_topic_id = $topic->id;
        $forum->last_topic_name = $topic->name;
        // Save
        $forum->save();
        // Post To Chatbox
        $appurl = \config('app.url');
        $postUrl = \sprintf('%s/forums/topics/%s?page=%s#post-%s', $appurl, $topic->id, $post->getPageNumber(), $post->id);
        $realUrl = \sprintf('/forums/topics/%s?page=%s#post-%s', $topic->id, $post->getPageNumber(), $post->id);
        $profileUrl = \sprintf('%s/users/%s', $appurl, $user->username);

        if (\config('other.staff-forum-notify') && ($forum->id == \config('other.staff-forum-id') || $forum->parent_id == \config('other.staff-forum-id'))) {
            $topic->notifyStaffers($user, $topic, $post);
        } else {
            $this->chatRepository->systemMessage(\sprintf('[url=%s]%s[/url] has left a reply on topic [url=%s]%s[/url]', $profileUrl, $user->username, $postUrl, $topic->name));
            // Notify All Subscribers Of New Reply
            if ($topic->first_user_poster_id != $user->id) {
                $topic->notifyStarter($user, $topic, $post);
            }
            $topic->notifySubscribers($user, $topic, $post);
        }
        //Achievements
        $user->unlock(new UserMadeFirstPost(), 1);
        $user->addProgress(new UserMade25Posts(), 1);
        $user->addProgress(new UserMade50Posts(), 1);
        $user->addProgress(new UserMade100Posts(), 1);
        $user->addProgress(new UserMade200Posts(), 1);
        $user->addProgress(new UserMade300Posts(), 1);
        $user->addProgress(new UserMade400Posts(), 1);
        $user->addProgress(new UserMade500Posts(), 1);
        $user->addProgress(new UserMade600Posts(), 1);
        $user->addProgress(new UserMade700Posts(), 1);
        $user->addProgress(new UserMade800Posts(), 1);
        $user->addProgress(new UserMade900Posts(), 1);

        return \redirect()->to($realUrl)
            ->withSuccess('Post Successfully Posted');
    }

    /**
     * Edit Post Form.
     */
    public function postEditForm(Topic $id, Post $postId): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $topic = Topic::findOrFail($id);
        $forum = $topic->forum;
        $category = $forum->getCategory();
        $post = Post::findOrFail($postId);

        return \view('forum.post_edit', [
            'topic'    => $topic,
            'forum'    => $forum,
            'post'     => $post,
            'category' => $category,
        ]);
    }

    /**
     * Edit A Post In A Topic.
     */
    public function postEdit(Request $request, Post $postId): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $post = Post::findOrFail($postId);
        $postUrl = \sprintf('forums/topics/%s?page=%s#post-%s', $post->topic->id, $post->getPageNumber(), $postId);

        \abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);
        $post->content = $request->input('content');
        $post->save();

        return \redirect()->to($postUrl)
            ->withSuccess('Post Successfully Edited!');
    }

    /**
     * Delete A Post.
     *
     *
     *@throws \Exception
     */
    public function postDelete(Request $request, Post $postId): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $post = Post::with('topic')->findOrFail($postId);

        \abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);
        $post->delete();

        return \redirect()->route('forum_topic', ['id' => $post->topic->id])
            ->withSuccess('This Post Is Now Deleted!');
    }
}
