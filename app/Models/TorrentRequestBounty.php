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
 * App\Models\TorrentRequestBounty.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property float                           $seedbonus
 * @property int                             $requests_id
 * @property int                             $anon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TorrentRequest $request
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereAnon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereRequestsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereSeedbonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TorrentRequestBounty whereUserId($value)
 * @mixin \Eloquent
 */
class TorrentRequestBounty extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected $table = 'request_bounty';

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
     * Belongs To A Torrent Request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TorrentRequest::class);
    }
}
