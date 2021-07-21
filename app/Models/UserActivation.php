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
 * App\Models\UserActivation.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivation whereUserId($value)
 * @mixin \Eloquent
 */
class UserActivation extends Model
{
    use HasFactory;
    use Auditable;

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
}
