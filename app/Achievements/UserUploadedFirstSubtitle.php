<?php

declare(strict_types=1);

namespace App\Achievements;

use Assada\Achievements\Achievement;

class UserUploadedFirstSubtitle extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'UserUploadedFirstSubtitle';

    /*
     * A small description for the achievement
     */
    public $description = 'Congratulations! You have made your first subtitle upload!';

    /*
    * A small description for the award
    */
    //public $award = "1000 Bonus Points";

    /*
    * Triggers whenever an Achiever unlocks this achievement
    */
    /*public function whenUnlocked($progress)
    {
        $achiever = $progress->achiever;

        $user = User::findOrFail($achiever->id);
        $user->seedbonus += 1000;
        $user->save();
    }*/
}
