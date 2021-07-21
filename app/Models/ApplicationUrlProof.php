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
 * App\Models\ApplicationUrlProof.
 *
 * @property int                             $id
 * @property int                             $application_id
 * @property string                          $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application $application
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApplicationUrlProof whereUrl($value)
 * @mixin \Eloquent
 */
class ApplicationUrlProof extends Model
{
    use HasFactory;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'url',
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
