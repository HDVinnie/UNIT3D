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
 * App\Models\Wish.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $title
 * @property string                          $imdb
 * @property string                          $type
 * @property string|null                     $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereImdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wish whereUserId($value)
 * @mixin \Eloquent
 */
class Wish extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Attributes That Aren't Mass Assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }
}
