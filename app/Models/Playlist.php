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
 * App\Models\Playlist.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $name
 * @property string                          $description
 * @property string|null                     $cover_image
 * @property int|null                        $position
 * @property int                             $is_private
 * @property int                             $is_pinned
 * @property int                             $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaylistTorrent[] $torrents
 * @property-read int|null $torrents_count
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereIsPinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Playlist whereUserId($value)
 * @mixin \Eloquent
 */
class Playlist extends Model
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
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Has Many Torrents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function torrents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlaylistTorrent::class);
    }

    /**
     * Has Many Comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'playlist_id');
    }
}
