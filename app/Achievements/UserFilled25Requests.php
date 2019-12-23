<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Achievements;

use Gstt\Achievements\Achievement;

final class UserFilled25Requests extends Achievement
{
    /**
     * The achievement name
     *
     * @var string
     */
    public string $name = 'Filled25Requests';

    /**
     * A small description for the achievement
     *
     * @var string
     */
    public string $description = 'Congrats! You have already filled 25 requests!';

    /**
     * The amount of "points" this user need to obtain in order to complete this achievement
     *
     * @var int
     */
    public int $points = 25;
}
