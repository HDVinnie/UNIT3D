<?php

declare(strict_types=1);
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Resolution.
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property int    $position
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TorrentRequest[] $requests
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Torrent[] $torrents
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Resolution whereSlug($value)
 * @mixin \Eloquent
 *
 * @property-read int|null $requests_count
 * @property-read int|null $torrents_count
 */
class Resolution extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Has Many Torrents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function torrents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Torrent::class);
    }

    /**
     * Has Many Torrent Requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TorrentRequest::class);
    }
}
