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
use Kyslik\ColumnSortable\Sortable;

/**
 * App\Models\History.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string|null                     $agent
 * @property string                          $info_hash
 * @property int|null                        $uploaded
 * @property int|null                        $actual_uploaded
 * @property int|null                        $client_uploaded
 * @property int|null                        $downloaded
 * @property int|null                        $actual_downloaded
 * @property int|null                        $client_downloaded
 * @property int                             $seeder
 * @property int                             $active
 * @property int                             $seedtime
 * @property int                             $immune
 * @property int                             $hitrun
 * @property int                             $prewarn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property-read \App\Models\Torrent $torrent
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereActualDownloaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereActualUploaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereClientDownloaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereClientUploaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereDownloaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereHitrun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereImmune($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereInfoHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History wherePrewarn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereSeeder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereSeedtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereUploaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereUserId($value)
 * @mixin \Eloquent
 */
class History extends Model
{
    use HasFactory;
    use Sortable;

    /**
     * The Columns That Are Sortable.
     *
     * @var array
     */
    public array $sortable = [
        'id',
        'agent',
        'active',
        'seeder',
        'uploaded',
        'downloaded',
        'seedtime',
        'created_at',
        'updated_at',
        'completed_at',
        'prewarn',
        'hitrun',
        'immune',
    ];

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected $table = 'history';

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'info_hash',
    ];

    /**
     * The Attributes That Should Be Mutated To Dates.
     *
     * @var array
     */
    protected $dates = ['completed_at'];

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
     * Belongs To A Torrent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function torrent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Torrent::class, 'info_hash', 'info_hash');
    }
}
