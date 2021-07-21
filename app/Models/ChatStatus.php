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
 * App\Models\ChatStatus.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $color
 * @property string|null                     $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ChatStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChatStatus extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * A Status Has Many Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'chat_status_id', 'id');
    }
}
