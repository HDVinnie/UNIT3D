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
use Illuminate\Support\Str;

/**
 * App\Models\Poll.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $title
 * @property string                          $slug
 * @property int                             $multiple_choice
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Option[] $options
 * @property-read int|null $options_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Voter[] $voters
 * @property-read int|null $voters_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereMultipleChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Poll whereUserId($value)
 * @mixin \Eloquent
 */
class Poll extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'multiple_choice',
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
     * A Poll Has Many Options.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * A Poll Has Many Voters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Voter::class);
    }

    /**
     * Set The Poll's Title.
     *
     * @param $title
     *
     * @return string
     */
    public function setTitleAttribute($title): string
    {
        return $this->attributes['title'] = $title;
    }

    /**
     * Create A Poll Title Slug.
     *
     * @param $title
     *
     * @return string
     */
    public function makeSlugFromTitle($title): string
    {
        $slug = \strlen($title) > 20 ? \substr(Str::slug($title), 0, 20) : Str::slug($title);
        $count = $this->where('slug', 'LIKE', '%'.$slug.'%')->count();

        return $count ? \sprintf('%s-%s', $slug, $count) : $slug;
    }

    /**
     * Get Total Votes On A Poll Option.
     *
     * @return int|string
     */
    public function totalVotes(): int | string
    {
        $result = 0;
        foreach ($this->options as $option) {
            $result += $option->votes;
        }

        return $result;
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($poll) {
            if (empty($poll->slug)) {
                $poll->slug = $poll->makeSlugFromTitle($poll->title);
            }

            return true;
        });
    }
}
