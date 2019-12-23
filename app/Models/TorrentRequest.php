<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     Mr.G
 */

namespace App\Models;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\Bbcode;
use App\Notifications\NewComment;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TorrentRequest.
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property \App\Models\Type $type
 * @property string|null $imdb
 * @property string|null $tvdb
 * @property string|null $tmdb
 * @property string|null $mal
 * @property string $description
 * @property int $user_id
 * @property float $bounty
 * @property int $votes
 * @property int|null $claimed
 * @property int $anon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $filled_by
 * @property string|null $filled_hash
 * @property \Illuminate\Support\Carbon|null $filled_when
 * @property int $filled_anon
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_when
 * @property-read \App\Models\User|null $FillUser
 * @property-read \App\Models\User|null $approveUser
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TorrentRequestBounty[] $requestBounty
 * @property-read \App\Models\Torrent|null $torrent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereAnon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereApprovedWhen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereBounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereClaimed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereFilledAnon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereFilledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereFilledHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereFilledWhen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereImdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereMal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereTmdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereTvdb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereVotes($value)
 * @mixin \Eloquent
 * @property string $igdb
 * @property-read int|null $comments_count
 * @property-read int|null $request_bounty_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequest whereIgdb($value)
 */
final class TorrentRequest extends Model
{
    use Auditable;

    /**
     * The Attributes That Should Be Mutated To Dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at',
        'updated_at',
        'filled_when',
        'approved_when',
    ];

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected string $table = 'requests';
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    private $guard;
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
        parent::__construct();
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approveUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function FillUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Belongs To A Type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * Belongs To A Torrent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function torrent(): BelongsTo
    {
        return $this->belongsTo(Torrent::class, 'filled_hash', 'info_hash');
    }

    /**
     * Has Many Comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'requests_id', 'id');
    }

    /**
     * Has Many BON Bounties.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestBounty(): HasMany
    {
        return $this->hasMany(TorrentRequestBounty::class, 'requests_id', 'id');
    }

    /**
     * Set The Requests Description After Its Been Purified.
     *
     * @param  string  $value
     *
     * @return void
     */
    public function setDescriptionAttribute(string $value): void
    {
        $this->attributes['description'] = htmlspecialchars($value);
    }

    /**
     * Parse Description And Return Valid HTML.
     *
     * @return string Parsed BBCODE To HTML
     */
    public function getDescriptionHtml(): string
    {
        $bbcode = new Bbcode();

        return $bbcode->parse($this->description, true);
    }

    /**
     * Notify Requester When A New Action Is Taken.
     *
     * @param $type
     * @param $payload
     * @return bool
     */
    public function notifyRequester($type, $payload): bool
    {
        $user = User::with('notification')->findOrFail($this->user_id);
        if ($user->acceptsNotification($this->guard->user(), $user, 'request', 'show_request_comment')) {
            $user->notify(new NewComment('request', $payload));

            return true;
        }

        return true;
    }
}
