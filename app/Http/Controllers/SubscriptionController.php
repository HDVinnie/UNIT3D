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

use App\Models\Forum;
use App\Models\Subscription;
use App\Models\Topic;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\SubscriptionControllerTest
 */
class SubscriptionController extends Controller
{
    /**
     * Subscribe To A Topic.
     */
    public function subscribeTopic(Request $request, string $route, Topic $topic): \Illuminate\Http\RedirectResponse
    {
        if ($route === 'subscriptions') {
            $logger = 'forum_subscriptions';
            $params = [];
        }
        if (! isset($logger)) {
            $logger = 'forum_topic';
            $params = ['id' => $topic->id];
        }

        if (! $request->user()->isSubscribed('topic', $topic->id)) {
            $subscription = new Subscription();
            $subscription->user_id = $request->user()->id;
            $subscription->topic_id = $topic->id;
            $subscription->save();

            return \redirect()->route($logger, $params)
                ->withSuccess('You are now subscribed to topic, '.$topic->name.'. You will now receive site notifications when a reply is left.');
        }

        return \redirect()->route($logger, $params)
            ->withErrors('You are already subscribed to this topic');
    }

    /**
     * Unsubscribe To A Topic.
     */
    public function unsubscribeTopic(Request $request, string $route, Topic $topic): \Illuminate\Http\RedirectResponse
    {
        if ($route === 'subscriptions') {
            $logger = 'forum_subscriptions';
            $params = [];
        }
        if (! isset($logger)) {
            $logger = 'forum_topic';
            $params = ['id' => $topic->id];
        }

        if ($request->user()->isSubscribed('topic', $topic->id)) {
            $subscription = $request->user()->subscriptions()->where('topic_id', '=', $topic->id)->first();
            $subscription->delete();

            return \redirect()->route($logger, $params)
                ->withSuccess('You are no longer subscribed to topic, '.$topic->name.'. You will no longer receive site notifications when a reply is left.');
        }

        return \redirect()->route($logger, $params)
            ->withErrors('You are not subscribed this topic to begin with...');
    }

    /**
     * Subscribe To A Forum.
     */
    public function subscribeForum(Request $request, string $route, Forum $forum): \Illuminate\Http\RedirectResponse
    {
        if ($route === 'subscriptions') {
            $logger = 'forum_subscriptions';
            $params = [];
        }
        if (! isset($logger)) {
            $logger = 'forums.show';
            $params = ['id' => $forum->id];
        }

        if (! $request->user()->isSubscribed('forum', $forum->id)) {
            $subscription = new Subscription();
            $subscription->user_id = $request->user()->id;
            $subscription->forum_id = $forum->id;
            $subscription->save();

            return \redirect()->route($logger, $params)
                ->withSuccess('You are now subscribed to forum, '.$forum->name.'. You will now receive site notifications when a topic is started.');
        }

        return \redirect()->route($logger, $params)
            ->withErrors('You are already subscribed to this forum');
    }

    /**
     * Unsubscribe To A Forum.
     */
    public function unsubscribeForum(Request $request, string $route, Forum $forum): \Illuminate\Http\RedirectResponse
    {
        if ($route === 'subscriptions') {
            $logger = 'forum_subscriptions';
            $params = [];
        }
        if (! isset($logger)) {
            $logger = 'forums.show';
            $params = ['id' => $forum->id];
        }

        if ($request->user()->isSubscribed('forum', $forum->id)) {
            $subscription = $request->user()->subscriptions()->where('forum_id', '=', $forum->id)->first();
            $subscription->delete();

            return \redirect()->route($logger, $params)
                ->withSuccess('You are no longer subscribed to forum, '.$forum->name.'. You will no longer receive site notifications when a topic is started.');
        }

        return \redirect()->route($logger, $params)
            ->withErrors('You are not subscribed this forum to begin with...');
    }
}
