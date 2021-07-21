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
 * App\Models\Group.
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property int    $position
 * @property int    $level
 * @property string $color
 * @property string $icon
 * @property string $effect
 * @property int    $is_internal
 * @property int    $is_owner
 * @property int    $is_admin
 * @property int    $is_modo
 * @property int    $is_trusted
 * @property int    $is_immune
 * @property int    $is_freeleech
 * @property int    $is_double_upload
 * @property int    $can_upload
 * @property int    $is_incognito
 * @property int    $autogroup
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereAutogroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereCanUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsDoubleUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsFreeleech($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsImmune($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsIncognito($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsModo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsTrusted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereSlug($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The Attributes That Aren't Mass Assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates If The Model Should Be Timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Has Many Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Has Many Permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Returns The Requested Row From The Permissions Table.
     *
     * @param $forum
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getPermissionsByForum($forum): Model | object | \Illuminate\Database\Eloquent\Builder | null
    {
        return Permission::where('forum_id', '=', $forum->id)
            ->where('group_id', '=', $this->id)
            ->first();
    }

    /**
     * Get the Group allowed answer as bool.
     *
     * @param $object
     * @param $groupId
     *
     * @return bool
     */
    public function isAllowed($object, $groupId): bool
    {
        if (\is_array($object) && \is_array($object['default_groups']) && \array_key_exists($groupId, $object['default_groups'])) {
            return $object['default_groups'][$groupId] == 1;
        }

        return true;
    }
}
