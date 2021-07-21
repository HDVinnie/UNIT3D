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

/**
 * App\Models\ApplicationImageProof.
 *
 * @property int                             $id
 * @property int                             $application_id
 * @property string                          $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application $application
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationImageProof whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApplicationImageProof extends Model
{
    use HasFactory;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'image',
    ];

    /**
     * Belongs To A Application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
