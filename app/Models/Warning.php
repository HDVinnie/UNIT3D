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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Warning.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $warned_by
 * @property int                             $torrent
 * @property string                          $reason
 * @property string|null                     $expires_on
 * @property int                             $active
 * @property int|null                        $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\User $staffuser
 * @property-read \App\Models\Torrent $torrenttitle
 * @property-read \App\Models\User $warneduser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereExpiresOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereTorrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereWarnedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning withoutTrashed()
 * @mixin \Eloquent
 */
class Warning extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    /**
     * Belongs To A Torrent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function torrenttitle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Torrent::class, 'torrent');
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warneduser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A USer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staffuser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'warned_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A USer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }
}
