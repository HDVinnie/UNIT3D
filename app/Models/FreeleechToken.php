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
 * App\Models\FreeleechToken.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $torrent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken whereTorrentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FreeleechToken whereUserId($value)
 * @mixin \Eloquent
 */
class FreeleechToken extends Model
{
    use HasFactory;
    use Auditable;
}
