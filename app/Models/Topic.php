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

namespace App\Models;

use App\Notifications\NewPost;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Topic.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $slug
 * @property string|null                     $state
 * @property int                             $pinned
 * @property int                             $approved
 * @property int                             $denied
 * @property int                             $solved
 * @property int                             $invalid
 * @property int                             $bug
 * @property int                             $suggestion
 * @property int                             $implemented
 * @property int|null                        $num_post
 * @property int|null                        $first_post_user_id
 * @property int|null                        $last_post_user_id
 * @property string|null                     $first_post_user_username
 * @property string|null                     $last_post_user_username
 * @property \Illuminate\Support\Carbon|null $last_reply_at
 * @property int|null                        $views
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int                             $forum_id
 * @property-read \App\Models\Forum $forum
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $posts
 * @property-read int|null $posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereBug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereDenied($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereFirstPostUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereFirstPostUserUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereForumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereImplemented($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereLastPostUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereLastPostUserUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereLastReplyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereNumPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic wherePinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereSolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereSuggestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereViews($value)
 * @mixin \Eloquent
 */
class Topic extends Model
{
    use HasFactory;
    use Auditable;

    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    /**
     * Belongs To A Forum.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'first_post_user_id', 'id');
    }

    /**
     * Has Many Posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Has Many Subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Notify Subscribers Of A Topic When New Post Is Made.
     *
     * @param $poster
     * @param $topic
     * @param $post
     *
     * @return string|null
     */
    public function notifySubscribers($poster, $topic, $post): ?string
    {
        $subscribers = User::selectRaw('distinct(users.id),max(users.username) as username,max(users.group_id) as group_id')->with('group')->where('users.id', '!=', $poster->id)
            ->join('subscriptions', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('user_notifications', 'user_notifications.user_id', '=', 'users.id')
            ->where('subscriptions.topic_id', '=', $topic->id)
            ->whereRaw('(user_notifications.show_subscription_topic = 1 OR user_notifications.show_subscription_topic is null)')
            ->groupBy('users.id')->get();

        foreach ($subscribers as $subscriber) {
            if ($subscriber->acceptsNotification($poster, $subscriber, 'subscription', 'show_subscription_topic')) {
                $subscriber->notify(new NewPost('subscription', $poster, $post));
            }
        }
    }

    /**
     * Notify Staffers When New Staff Post Is Made.
     *
     * @param $poster
     * @param $topic
     * @param $post
     *
     * @return string|null
     */
    public function notifyStaffers($poster, $topic, $post): ?string
    {
        $staffers = User::leftJoin('groups', 'users.group_id', '=', 'groups.id')
            ->select('users.id')
            ->where('users.id', '<>', $poster->id)
            ->where('groups.is_modo', 1)
            ->get();

        foreach ($staffers as $staffer) {
            $staffer->notify(new NewPost('staff', $poster, $post));
        }
    }

    /**
     * Does User Have Permission To View Topic.
     *
     * @return \App\Models\Forum|bool|string
     */
    public function viewable(): Forum | bool | string
    {
        if (\auth()->user()->group->is_modo) {
            return true;
        }

        return $this->forum->getPermission()->read_topic;
    }

    /**
     * Notify Starter When An Action Is Taken.
     *
     * @param $poster
     * @param $topic
     * @param $post
     *
     * @return bool
     */
    public function notifyStarter($poster, $topic, $post): bool
    {
        $user = User::find($topic->first_post_user_id);
        if ($user->acceptsNotification(\auth()->user(), $user, 'forum', 'show_forum_topic')) {
            $user->notify(new NewPost('topic', $poster, $post));
        }

        return true;
    }

    /**
     * Get Post Number From ID.
     *
     * @param $searchId
     *
     * @return int
     */
    public function postNumberFromId($searchId): int
    {
        $count = 0;
        foreach ($this->posts as $post) {
            $count++;
            if ($searchId == $post->id) {
                break;
            }
        }

        return $count;
    }
}
