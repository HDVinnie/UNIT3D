<?php
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

namespace App\Subscribers;

use App\Traits\AuthChecker;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuthEventSubscriber
{
    use AuthChecker;

    /**
     * Register The Listeners For The Subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(Login::class, [$this, 'onUserLogin']);
        $events->listen(Failed::class, [$this, 'onUserLoginFailed']);
        $events->listen(Lockout::class, [$this, 'onUserLoginLockout']);
        $events->listen(Logout::class, [$this, 'onUserLogout']);
    }

    /**
     * Handle User Login Events.
     *
     * @param \Illuminate\Auth\Events\Login $event
     */
    public function onUserLogin(Login $event)
    {
        $user = $event->user;

        if (! is_null($user)) {
            $this->handleLogin($user);
        }
    }

    /**
     * Handle User Login Failed Events.
     *
     * @param \Illuminate\Auth\Events\Failed $event
     */
    public function onUserLoginFailed(Failed $event)
    {
        $user = $event->user;

        if (! is_null($user)) {
            $this->handleFailed($user);
        }
    }

    /**
     * Handle User Lockout Events.
     *
     * @param \Illuminate\Auth\Events\Lockout $event
     */
    public function onUserLoginLockout(Lockout $event)
    {
        $payload = $event->request->all();

        if (! empty($payload)) {
            $this->handleLockout($payload);
        }
    }

    /**
     * Handle User Logout Events.
     *
     * @param \Illuminate\Auth\Events\Logout $event
     */
    public function onUserLogout(Logout $event)
    {
        // $user = $event->user;
    }
}
