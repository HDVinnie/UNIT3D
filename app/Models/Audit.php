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
 * App\Models\Audit.
 *
 * @property int                             $id
 * @property int|null                        $user_id
 * @property string                          $model_name
 * @property int                             $model_entry_id
 * @property string                          $action
 * @property mixed                           $record
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereModelEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereRecord($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Audit whereUserId($value)
 * @mixin \Eloquent
 */
class Audit extends Model
{
    use HasFactory;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'model_name', 'model_entry_id', 'action', 'record',
    ];

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
