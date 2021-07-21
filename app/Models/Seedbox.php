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
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Seedbox.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $name
 * @property string                          $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Seedbox whereUserId($value)
 * @mixin \Eloquent
 */
class Seedbox extends Model
{
    use HasFactory;
    use Encryptable;
    use Auditable;

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected $table = 'clients';

    /**
     * The Attributes That Are Encrypted.
     *
     * @var array
     */
    protected array $encryptable = [
        'ip',
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
}
