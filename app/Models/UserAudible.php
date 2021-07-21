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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserAudible.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int|null                        $room_id
 * @property int|null                        $target_id
 * @property int|null                        $bot_id
 * @property int                             $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bot|null $bot
 * @property-read \App\Models\Chatroom|null $room
 * @property-read \App\Models\User|null $target
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAudible whereUserId($value)
 * @mixin \Eloquent
 */
class UserAudible extends Model
{
    use HasFactory;

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Belongs To A Chatroom.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chatroom::class);
    }

    /**
     * Belongs To A Target.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function target(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Belongs To A Bot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }
}
