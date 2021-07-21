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
 * App\Models\Ban.
 *
 * @property int                             $id
 * @property int                             $owned_by
 * @property int|null                        $created_by
 * @property string|null                     $ban_reason
 * @property string|null                     $unban_reason
 * @property string|null                     $removed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $banneduser
 * @property-read \App\Models\User|null $staffuser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereBanReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereOwnedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereRemovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereUnbanReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ban whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ban extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function banneduser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owned_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staffuser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }
}
