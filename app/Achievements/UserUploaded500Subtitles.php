<?php

declare(strict_types=1);

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserUploaded500Subtitles extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'UserUploaded500Subtitles';

    /*
     * A small description for the achievement
     */
    public $description = 'You have made 500 subtitle uploads!';

    /*
    * The amount of "points" this user need to obtain in order to complete this achievement
    */
    public $points = 500;
}
