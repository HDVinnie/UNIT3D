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
 * App\Models\Report.
 *
 * @property int                             $id
 * @property string                          $type
 * @property int                             $reporter_id
 * @property int|null                        $staff_id
 * @property string                          $title
 * @property string                          $message
 * @property int                             $solved
 * @property string|null                     $verdict
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int                             $reported_user
 * @property int                             $torrent_id
 * @property int                             $request_id
 * @property-read \App\Models\User $reported
 * @property-read \App\Models\User $reporter
 * @property-read \App\Models\TorrentRequest $request
 * @property-read \App\Models\User|null $staff
 * @property-read \App\Models\Torrent $torrent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereReportedUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereSolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereTorrentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Report whereVerdict($value)
 * @mixin \Eloquent
 */
class Report extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Attributes That Aren't Mass Assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Belongs To A Request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TorrentRequest::class, 'request_id');
    }

    /**
     * Belongs To A Torrent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function torrent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Torrent::class, 'torrent_id');
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reporter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reported(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Staff Member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id')->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }
}
