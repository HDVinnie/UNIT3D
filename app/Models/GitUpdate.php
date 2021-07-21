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
 * App\Models\GitUpdate.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GitUpdate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GitUpdate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
