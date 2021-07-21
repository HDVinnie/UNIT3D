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

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserNotification.
 *
 * @property int   $id
 * @property int   $user_id
 * @property int   $show_bon_gift
 * @property int   $show_mention_forum_post
 * @property int   $show_mention_article_comment
 * @property int   $show_mention_request_comment
 * @property int   $show_mention_torrent_comment
 * @property int   $show_subscription_topic
 * @property int   $show_subscription_forum
 * @property int   $show_forum_topic
 * @property int   $show_following_upload
 * @property int   $show_request_bounty
 * @property int   $show_request_comment
 * @property int   $show_request_fill
 * @property int   $show_request_fill_approve
 * @property int   $show_request_fill_reject
 * @property int   $show_request_claim
 * @property int   $show_request_unclaim
 * @property int   $show_torrent_comment
 * @property int   $show_torrent_tip
 * @property int   $show_torrent_thank
 * @property int   $show_account_follow
 * @property int   $show_account_unfollow
 * @property array $json_account_groups
 * @property array $json_bon_groups
 * @property array $json_mention_groups
 * @property array $json_request_groups
 * @property array $json_torrent_groups
 * @property array $json_forum_groups
 * @property array $json_following_groups
 * @property array $json_subscription_groups
 * @property-read array $expected_fields
 * @property-read array $expected_groups
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonAccountGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonBonGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonFollowingGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonForumGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonMentionGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonRequestGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonSubscriptionGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereJsonTorrentGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowAccountFollow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowAccountUnfollow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowBonGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowFollowingUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowForumTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowMentionArticleComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowMentionForumPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowMentionRequestComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowMentionTorrentComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestBounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestFill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestFillApprove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestFillReject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowRequestUnclaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowSubscriptionForum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowSubscriptionTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowTorrentComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowTorrentThank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereShowTorrentTip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserNotification whereUserId($value)
 * @mixin \Eloquent
 */
class UserNotification extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The Attributes That Should Be Cast To Native Values.
     *
     * @var array
     */
    protected $casts = [
        'json_account_groups'      => 'array',
        'json_mention_groups'      => 'array',
        'json_request_groups'      => 'array',
        'json_torrent_groups'      => 'array',
        'json_forum_groups'        => 'array',
        'json_following_groups'    => 'array',
        'json_subscription_groups' => 'array',
        'json_bon_groups'          => 'array',
    ];

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Get the Expected groups for form validation.
     *
     * @return array
     */
    public function getExpectedGroupsAttribute(): array
    {
        return ['default_groups' => ['1' => 0]];
    }

    /**
     * Get the Expected fields for form validation.
     *
     * @return array
     */
    public function getExpectedFieldsAttribute(): array
    {
        return [];
    }

    /**
     * Set the base vars on object creation without touching boot.
     *
     * @param string $type
     *
     * @return void
     */
    public function setDefaultValues(string $type = 'default'): void
    {
        foreach ($this->casts as $k => $v) {
            if ($v === 'array') {
                $this->$k = $this->expected_groups;
            }
        }
    }
}
