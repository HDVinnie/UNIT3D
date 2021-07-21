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
 * App\Models\PersonalFreeleech.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PersonalFreeleech whereUserId($value)
 * @mixin \Eloquent
 */
class PersonalFreeleech extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected $table = 'personal_freeleech';
}
