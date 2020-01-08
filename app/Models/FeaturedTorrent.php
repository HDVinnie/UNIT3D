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

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeaturedTorrent.
 *
 * @property int $id
 * @property int $user_id
 * @property int $torrent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Torrent $torrent
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent whereTorrentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FeaturedTorrent whereUserId($value)
 * @mixin \Eloquent
 */
final class FeaturedTorrent extends Model
{
    use Auditable;

    /**
     * Belongs To A Torrent.
     *
     * @return BelongsTo
     */
    public function torrent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Torrent::class);
    }

    /**
     * Belongs To A User.
     *
     * @return BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
