<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
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

final class PostController extends Controller
{
    /**
     * @var TaggedUserRepository
     */
    private TaggedUserRepository $tag;

    /**
     * @var ChatRepository
     */
    private ChatRepository $chat;
    /**
     * @var \Illuminate\Routing\Redirector
     */
    private $redirector;
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $configRepository;
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;

    /**
     * ForumController Constructor.
     *
     * @param TaggedUserRepository $tag
     * @param ChatRepository       $chat
     */
    public function __construct(TaggedUserRepository $tag, ChatRepository $chat, Redirector $redirector, Repository $configRepository, Factory $viewFactory)
    {
        $this->tag = $tag;
        $this->chat = $chat;
        $this->redirector = $redirector;
        $this->configRepository = $configRepository;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Store A New Post To A Topic.
     *
     * @param \Illuminate\Http\Request  $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function reply(Request $request, $id)
    {
        $user = $request->user();
        $topic = Topic::findOrFail($id);
        $forum = $topic->forum;
        $category = $forum->getCategory();

        // The user has the right to create a post here?
        if (! $category->getPermission()->reply_topic || ($topic->state == 'close' && ! $request->user()->group->is_modo)) {
            return $this->redirector->route('forums.index')
                ->withErrors('You Cannot Reply To This Topic!');
        }

        $post = new Post();
        $post->content = $request->input('content');
        $post->user_id = $user->id;
        $post->topic_id = $topic->id;

        $v = validator($post->toArray(), [
            'content' => 'required|min:1',
            'user_id' => 'required',
            'topic_id' => 'required',
        ]);

        if ($v->fails()) {
            return $this->redirector->route('forum_topic', ['id' => $topic->id])
                ->withErrors($v->errors());
        } else {
            $post->save();

            $appurl = $this->configRepository->get('app.url');
            $href = sprintf('%s/forums/topics/%s?page=%s#post-%s', $appurl, $topic->id, $post->getPageNumber(), $post->id);
            $message = sprintf('%s has tagged you in a forum post. You can view it [url=%s] HERE [/url]', $user->username, $href);

            if ($this->tag->hasTags($request->input('content'))) {
                if ($this->tag->contains($request->input('content'), '@here') && $user->group->is_modo) {
                    $users = new Collection([]);

                    $topic->posts()->get()->each(function ($p) use ($users) {
                        $users->push($p->user);
                    });

                    $this->tag->messagePostUsers(
                        'forum',
                        $users,
                        $user,
                        'Staff',
                        $post
                    );
                } else {
                    $this->tag->messageTaggedPostUsers(
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
            $appurl = $this->configRepository->get('app.url');
            $postUrl = sprintf('%s/forums/topics/%s?page=%s#post-%s', $appurl, $topic->id, $post->getPageNumber(), $post->id);
            $realUrl = sprintf('/forums/topics/%s?page=%s#post-%s', $topic->id, $post->getPageNumber(), $post->id);
            $profileUrl = sprintf('%s/users/%s', $appurl, $user->username);
            $this->chat->systemMessage(sprintf('[url=%s]%s[/url] has left a reply on topic [url=%s]%s[/url]', $profileUrl, $user->username, $postUrl, $topic->name));

            // Notify All Subscribers Of New Reply
            if ($topic->first_user_poster_id != $user->id) {
                $topic->notifyStarter($user, $topic, $post);
            }
            $topic->notifySubscribers($user, $topic, $post);

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

            return $this->redirector->to($realUrl)
                ->withSuccess('Post Successfully Posted');
        }
    }

    /**
     * Edit Post Form.
     *
     * @param $id
     * @param $postId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function postEditForm($id, $postId): Factory
    {
        $topic = Topic::findOrFail($id);
        $forum = $topic->forum;
        $category = $forum->getCategory();
        $post = Post::findOrFail($postId);

        return $this->viewFactory->make('forum.post_edit', [
            'topic'    => $topic,
            'forum'    => $forum,
            'post'     => $post,
            'category' => $category,
        ]);
    }

    /**
     * Edit A Post In A Topic.
     *
     * @param \Illuminate\Http\Request $request
     * @param $postId
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function postEdit(Request $request, $postId): RedirectResponse
    {
        $user = $request->user();
        $post = Post::findOrFail($postId);
        $postUrl = sprintf('forums/topics/%s?page=%s#post-%s', $post->topic->id, $post->getPageNumber(), $postId);

        abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);
        $post->content = $request->input('content');
        $post->save();

        return $this->redirector->to($postUrl)
            ->withSuccess('Post Successfully Edited!');
    }

    /**
     * Delete A Post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param                            $postId
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, $postId): RedirectResponse
    {
        $user = $request->user();
        $post = Post::with('topic')->findOrFail($postId);

        abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);
        $post->delete();

        return $this->redirector->route('forum_topic', ['id' => $post->topic->id])
            ->withSuccess('This Post Is Now Deleted!');
    }
}
