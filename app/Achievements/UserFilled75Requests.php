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

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserFilled75Requests extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'Filled75Requests';

    /*
     * A small description for the achievement
     */
    public $description = 'Woot! You have already filled 75 requests!';

    /*
     * The amount of "points" this user need to obtain in order to complete this achievement
     */
    public $points = 75;
}
