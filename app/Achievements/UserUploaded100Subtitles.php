<?php

declare(strict_types=1);

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserUploaded100Subtitles extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'UserUploaded100Subtitles';

    /*
     * A small description for the achievement
     */
    public $description = 'You have made 100 subtitle uploads!';

    /*
    * The amount of "points" this user need to obtain in order to complete this achievement
    */
    public $points = 100;
}
