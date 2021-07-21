<?php

declare(strict_types=1);

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserUploaded700Subtitles extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'UserUploaded700Subtitles';

    /*
     * A small description for the achievement
     */
    public $description = 'You have made 700 subtitle uploads!';

    /*
    * The amount of "points" this user need to obtain in order to complete this achievement
    */
    public $points = 700;
}
