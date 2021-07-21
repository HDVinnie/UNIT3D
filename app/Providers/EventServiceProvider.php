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

namespace App\Providers;

use App\Events\CommentCreated;
use App\Events\TicketAssigned;
use App\Events\TicketClosed;
use App\Events\TicketCreated;
use App\Events\TicketWentStale;
use App\Listeners\AchievementUnlocked;
use App\Listeners\FailedLoginListener;
use App\Listeners\LoginListener;
use App\Listeners\NotifyStaffCommentWasCreated;
use App\Listeners\NotifyStaffTicketWasAssigned;
use App\Listeners\NotifyStaffTicketWasClosed;
use App\Listeners\NotifyStaffTicketWasCreated;
use App\Listeners\NotifyUserCommentWasCreated;
use App\Listeners\NotifyUserTicketIsStale;
use App\Listeners\NotifyUserTicketWasAssigned;
use App\Listeners\NotifyUserTicketWasClosed;
use App\Listeners\NotifyUserTicketWasCreated;
use App\Listeners\PasswordProtectBackup;
use Assada\Achievements\Event\Unlocked;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\Backup\Events\BackupZipWasCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Auth System
        Login::class => [
            LoginListener::class,
        ],
        Failed::class => [
            FailedLoginListener::class,
        ],

        // Achievements System
        Unlocked::class => [
            AchievementUnlocked::class,
        ],

        // Backups System
        BackupZipWasCreated::class => [
            PasswordProtectBackup::class,
        ],

        // Ticket System
        TicketCreated::class => [
            NotifyUserTicketWasCreated::class,
            NotifyStaffTicketWasCreated::class,
        ],
        CommentCreated::class => [
            NotifyUserCommentWasCreated::class,
            NotifyStaffCommentWasCreated::class,
        ],
        TicketClosed::class => [
            NotifyUserTicketWasClosed::class,
            NotifyStaffTicketWasClosed::class,
        ],
        TicketAssigned::class => [
            NotifyUserTicketWasAssigned::class,
            NotifyStaffTicketWasAssigned::class,
        ],
        TicketWentStale::class => [
            NotifyUserTicketIsStale::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }
}
