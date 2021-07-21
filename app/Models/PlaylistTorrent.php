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
 * App\Models\PlaylistTorrent.
 *
 * @property int      $id
 * @property int|null $position
 * @property int      $playlist_id
 * @property int      $torrent_id
 * @property int      $tmdb_id
 * @property-read \App\Models\Playlist $playlist
 * @property-read \App\Models\Torrent $torrent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent wherePlaylistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent whereTmdbId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PlaylistTorrent whereTorrentId($value)
 * @mixin \Eloquent
 */
class PlaylistTorrent extends Model
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
     * Belongs To A Torrent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function torrent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Torrent::class);
    }

    /**
     * Belongs To A Playlist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function playlist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }
}
