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

namespace App\Traits;

trait UsersOnlineTrait
{
    /**
     * Check if the current user is online.
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        if (! $this->last_action) {
            return false;
        }

        return $this->last_action->gt(\now()->subMinutes(5));
    }
}
