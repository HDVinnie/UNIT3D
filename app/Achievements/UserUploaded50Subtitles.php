<?php

declare(strict_types=1);

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserUploaded50Subtitles extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'UserUploaded50Subtitles';

    /*
     * A small description for the achievement
     */
    public $description = 'You have made 50 subtitle uploads!';

    /*
    * The amount of "points" this user need to obtain in order to complete this achievement
    */
    public $points = 50;
}
