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

namespace App\Notifications;

use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewReseedRequest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * NewReseedRequest Constructor.
     */
    public function __construct(public Torrent $torrent)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        $appurl = \config('app.url');

        return [
            'title' => 'New Reseed Request',
            'body'  => \sprintf('Some time ago, you downloaded: %s. Now its dead and someone has requested a reseed on it. If you still have this torrent in storage, please consider reseeding it!', $this->torrent->name),
            'url'   => \sprintf('%s/torrents/%s', $appurl, $this->torrent->id),
        ];
    }
}
