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

namespace App\Listeners;

use App\Models\User;
use App\Repositories\ChatRepository;
use Assada\Achievements\Event\Unlocked;
use Illuminate\Support\Facades\Session;

class AchievementUnlocked
{
    /**
     * AchievementUnlocked Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(Unlocked $unlocked): void
    {
        // There's an AchievementProgress instance located on $event->progress
        $user = User::where('id', '=', $unlocked->progress->achiever_id)->first();
        Session::flash('achievement', $unlocked->progress->details->name);

        if ($user->private_profile == 0) {
            $profileUrl = \href_profile($user);

            $this->chatRepository->systemMessage(
                \sprintf('User [url=%s]%s[/url] has unlocked the %s achievement :medal:', $profileUrl, $user->username, $unlocked->progress->details->name)
            );
        }
    }
}
