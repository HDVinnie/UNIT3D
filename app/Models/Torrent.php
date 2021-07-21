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

use App\Helpers\Bbcode;
use App\Helpers\Linkify;
use App\Helpers\MediaInfo;
use App\Helpers\StringHelper;
use App\Notifications\NewComment;
use App\Notifications\NewThank;
use App\Traits\Auditable;
use Hootlex\Moderation\Moderatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use voku\helper\AntiXSS;

/**
 * App\Models\Torrent.
 *
 * @property int                                                                    $id
 * @property string                                                                 $name
 * @property string                                                                 $slug
 * @property string                                                                 $description
 * @property string|null                                                            $mediainfo
 * @property string                                                                 $info_hash
 * @property string                                                                 $file_name
 * @property int                                                                    $num_file
 * @property float                                                                  $size
 * @property string|null                                                            $nfo
 * @property int                                                                    $leechers
 * @property int                                                                    $seeders
 * @property int                                                                    $times_completed
 * @property int|null                                                               $category_id
 * @property string                                                                 $announce
 * @property int                                                                    $user_id
 * @property string                                                                 $imdb
 * @property string                                                                 $tvdb
 * @property string                                                                 $tmdb
 * @property string                                                                 $mal
 * @property string                                                                 $igdb
 * @property int                                                                    $stream
 * @property int                                                                    $free
 * @property int                                                                    $doubleup
 * @property int                                                                    $highspeed
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\FeaturedTorrent[] $featured
 * @property int                                                                    $status
 * @property \Illuminate\Support\Carbon|null                                        $moderated_at
 * @property int|null                                                               $moderated_by
 * @property int                                                                    $anon
 * @property int                                                                    $sticky
 * @property int                                                                    $sd
 * @property int                                                                    $internal
 * @property \Illuminate\Support\Carbon|null                                        $created_at
 * @property \Illuminate\Support\Carbon|null                                        $updated_at
 * @property string|null                                                            $release_year
 * @property int                                                                    $type_id
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read int|null $featured_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TorrentFile[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\History[] $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Warning[] $hitrun
 * @property-read int|null $hitrun_count
 * @property-read \App\Models\User|null $moderated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Peer[] $peers
 * @property-read int|null $peers_count
 * @property-read \App\Models\TorrentRequest|null $request
 * @property-write mixed $media_info
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subtitle[] $subtitles
 * @property-read int|null $subtitles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Thank[] $thanks
 * @property-read int|null $thanks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BonTransactions[] $tips
 * @property-read int|null $tips_count
 * @property-read \App\Models\Type $type
 * @property-read \App\Models\User $uploader
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereAnnounce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereAnon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereDoubleup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereHighspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereIgdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereImdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereInfoHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereLeechers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereMal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereMediainfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereModeratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereModeratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereNfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereNumFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereReleaseYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereSd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereSeeders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereSticky($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereStream($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereTimesCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereTmdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereTvdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Torrent whereUserId($value)
 * @mixin \Eloquent
 */
class Torrent extends Model
{
    use HasFactory;
    use Moderatable;
    use Sortable;
    use Auditable;

    /**
     * The Columns That Are Sortable.
     *
     * @var array
     */
    public array $sortable = [
        'id',
        'name',
        'size',
        'seeders',
        'leechers',
        'times_completed',
        'created_at',
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

    /**
     * Belongs To A Uploader.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Not needed yet but may use this soon.

        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Belongs To A Type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * Belongs To A Resolution.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resolution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Resolution::class);
    }

    /**
     * Has Many Genres.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function genres(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'genre_torrent', 'torrent_id', 'genre_id', 'id', 'id');
    }

    /**
     * Torrent Has Been Moderated By.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moderated(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Has Many Keywords.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keywords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /**
     * Has Many History.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(History::class, 'info_hash', 'info_hash');
    }

    /**
     * Has Many Tips.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BonTransactions::class, 'torrent_id', 'id')->where('name', '=', 'tip');
    }

    /**
     * Has Many Thank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function thanks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Thank::class);
    }

    /**
     * Has Many HitRuns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hitrun(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Warning::class, 'torrent');
    }

    /**
     * Has Many Featured.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function featured(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeaturedTorrent::class);
    }

    /**
     * Has Many Files.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TorrentFile::class);
    }

    /**
     * Has Many Comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Has Many Peers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Peer::class);
    }

    /**
     * Has Many Subtitles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subtitles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subtitle::class);
    }

    /**
     * Relationship To A Single Request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function request(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TorrentRequest::class, 'filled_hash', 'info_hash');
    }

    /**
     * Set The Torrents Description After Its Been Purified.
     *
     * @param string $value
     *
     * @return void
     */
    public function setDescriptionAttribute(string $value): void
    {
        $this->attributes['description'] = \htmlspecialchars((new AntiXSS())->xss_clean($value), ENT_NOQUOTES);
    }

    /**
     * Parse Description And Return Valid HTML.
     *
     * @return string Parsed BBCODE To HTML
     */
    public function getDescriptionHtml(): string
    {
        $bbcode = new Bbcode();
        $linkify = new Linkify();

        return $linkify->linky($bbcode->parse($this->description, true));
    }

    /**
     * Set The Torrents MediaInfo After Its Been Purified.
     *
     * @param string $value
     *
     * @return void
     */
    public function setMediaInfoAttribute(string $value): void
    {
        $this->attributes['mediainfo'] = $value;
    }

    /**
     * Formats The Output Of The Media Info Dump.
     *
     * @return array
     */
    public function getMediaInfo(): array
    {
        return (new MediaInfo())->parse($this->mediaInfo);
    }

    /**
     * Returns The Size In Human Format.
     *
     * @param null $bytes
     * @param int  $precision
     *
     * @return string
     */
    public function getSize($bytes = null, int $precision = 2): string
    {
        $bytes = $this->size;

        return StringHelper::formatBytes($bytes, 2);
    }

    /**
     * Bookmarks.
     */
    public function bookmarked(): bool
    {
        return (bool) Bookmark::where('user_id', '=', \auth()->user()->id)
            ->where('torrent_id', '=', $this->id)
            ->first();
    }

    /**
     * Notify Uploader When An Action Is Taken.
     *
     * @param $type
     * @param $payload
     *
     * @return bool
     */
    public function notifyUploader($type, $payload): bool
    {
        $user = User::with('notification')->findOrFail($this->user_id);
        if ($type === 'thank') {
            if ($user->acceptsNotification(\auth()->user(), $user, 'torrent', 'show_torrent_thank')) {
                $user->notify(new NewThank('torrent', $payload));

                return true;
            }

            return true;
        }
        if ($user->acceptsNotification(\auth()->user(), $user, 'torrent', 'show_torrent_comment')) {
            $user->notify(new NewComment('torrent', $payload));

            return true;
        }

        return true;
    }

    /**
     * Torrent Is Freeleech.
     *
     * @param null $user
     *
     * @return bool
     */
    public function isFreeleech($user = null): bool
    {
        $pfree = $user && ($user->group->is_freeleech || PersonalFreeleech::where('user_id', '=', $user->id)->first());

        return $this->free || \config('other.freeleech') || $pfree;
    }
}
