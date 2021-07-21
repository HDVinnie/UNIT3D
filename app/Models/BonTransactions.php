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
 * App\Models\BonTransactions.
 *
 * @property int      $id
 * @property int      $itemID
 * @property string   $name
 * @property float    $cost
 * @property int      $sender
 * @property int      $receiver
 * @property int|null $torrent_id
 * @property int|null $post_id
 * @property string   $comment
 * @property string   $date_actioned
 * @property-read \App\Models\BonExchange $exchange
 * @property-read \App\Models\User $receiverObj
 * @property-read \App\Models\User $senderObj
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereDateActioned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereItemID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereReceiver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonTransactions whereTorrentId($value)
 * @mixin \Eloquent
 */
class BonTransactions extends Model
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
     * The Storage Format Of The Model's Date Columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * Belongs To A Sender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    // Bad name to not conflict with sender (not sender_id)

    public function senderObj(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender', 'id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Receiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    // Bad name to not conflict with sender (not sender_id)

    public function receiverObj(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver', 'id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To BonExchange.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchange(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BonExchange::class, 'itemID', 'id')->withDefault([
            'value' => 0,
            'cost'  => 0,
        ]);
    }
}
