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
 * App\Models\Bot.
 *
 * @property int                             $id
 * @property int                             $position
 * @property string                          $slug
 * @property string                          $name
 * @property string                          $command
 * @property string|null                     $color
 * @property string|null                     $icon
 * @property string|null                     $emoji
 * @property string|null                     $info
 * @property string|null                     $about
 * @property string|null                     $help
 * @property int                             $active
 * @property int                             $is_protected
 * @property int                             $is_triviabot
 * @property int                             $is_nerdbot
 * @property int                             $is_systembot
 * @property int                             $is_casinobot
 * @property int                             $is_betbot
 * @property int                             $uploaded
 * @property int                             $downloaded
 * @property int                             $fl_tokens
 * @property float                           $seedbonus
 * @property int                             $invites
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereCommand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereDownloaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereEmoji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereFlTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereHelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereInvites($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsBetbot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsCasinobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsNerdbot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsProtected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsSystembot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereIsTriviabot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereSeedbonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bot whereUploaded($value)
 * @mixin \Eloquent
 */
class Bot extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The Attributes That Should Be Cast To Native Types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
