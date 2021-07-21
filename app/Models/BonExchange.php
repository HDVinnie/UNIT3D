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
 * App\Models\BonExchange.
 *
 * @property int         $id
 * @property string|null $description
 * @property int         $value
 * @property int         $cost
 * @property bool        $upload
 * @property bool        $download
 * @property bool        $personal_freeleech
 * @property bool        $invite
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereDownload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereInvite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange wherePersonalFreeleech($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BonExchange whereValue($value)
 * @mixin \Eloquent
 */
class BonExchange extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Database Table Used By The Model.
     *
     * @var string
     */
    protected $table = 'bon_exchange';

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The Attributes That Should Be Casted To Native Types.
     *
     * @var array
     */
    protected $casts = [
        'upload'             => 'boolean',
        'download'           => 'boolean',
        'personal_freeleech' => 'boolean',
        'invite'             => 'boolean',
    ];

    /**
     * @method getDownloadOptions
     *
     * @return array
     */
    public function getDownloadOptions(): array
    {
        return self::where('download', '=', true)
            ->orderBy('value', 'asc')
            ->get();
    }

    /**
     * @method getUploadOptions
     *
     * @return array
     */
    public function getUploadOptions(): array
    {
        return self::where('upload', '=', true)
            ->orderBy('value', 'asc')
            ->get();
    }

    /**
     * @method getPersonalFreeleechOption
     *
     * @return array
     */
    public function getPersonalFreeleechOption(): array
    {
        return self::where('personal_freeleech', '=', true)
            ->orderBy('value', 'asc')
            ->get();
    }

    /**
     * @method getInviteOption
     *
     * @return array
     */
    public function getInviteOption(): array
    {
        return self::where('invite', '=', true)
            ->orderBy('value', 'asc')
            ->get();
    }

    /**
     * @method getItemCost
     *
     * @param $id
     *
     * @return int
     */
    public function getItemCost($id): int
    {
        return self::where('id', '=', $id)
            ->get()
            ->toArray()[0]['cost'];
    }
}
