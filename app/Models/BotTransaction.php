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
 * App\Models\BotTransaction.
 *
 * @property int                             $id
 * @property string|null                     $type
 * @property float                           $cost
 * @property int                             $user_id
 * @property int                             $bot_id
 * @property int                             $to_user
 * @property int                             $to_bot
 * @property string                          $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bot $bot
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereToBot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereToUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BotTransaction whereUserId($value)
 * @mixin \Eloquent
 */
class BotTransaction extends Model
{
    use HasFactory;
    use Auditable;

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

    // Bad name to not conflict with sender (not sender_id)

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Bot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    // Bad name to not conflict with sender (not sender_id)

    public function bot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bot::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Get the Bot transaction type answer as string.
     *
     * @return string
     */
    public function forHumans(): string
    {
        if ($this->type === 'bon') {
            return 'BON';
        }

        return 'Unknown';
    }
}
