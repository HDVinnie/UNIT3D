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
 * App\Models\FailedLoginAttempt.
 *
 * @property int                             $id
 * @property int|null                        $user_id
 * @property string                          $username
 * @property string                          $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedLoginAttempt whereUsername($value)
 * @mixin \Eloquent
 */
class FailedLoginAttempt extends Model
{
    use HasFactory;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'username',
        'ip_address',
    ];

    /**
     * @param $user
     * @param $username
     * @param $ip
     *
     * @return mixed
     */
    public static function record($user, $username, $ip): mixed
    {
        return static::create([
            'user_id'    => \is_null($user) ? null : $user->id,
            'username'   => $username,
            'ip_address' => $ip,
        ]);
    }
}
