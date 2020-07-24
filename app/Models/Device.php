<?php
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

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The Attributes That Should Be Casted To Native Types.
     *
     * @var array
     */
    protected $casts = [
        'is_trusted'   => 'boolean',
        'is_untrusted' => 'boolean',
        'is_desktop'   => 'boolean',
        'is_mobile'    => 'boolean',
    ];

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Has Many Authentications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authentications()
    {
        return $this->hasMany(Authentication::class);
    }

    /**
     * Has One Authentication.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function authentication()
    {
        $relation = $this->hasOne(Authentication::class);
        $relation->orderBy('created_at', 'desc');

        return $relation;
    }
}
