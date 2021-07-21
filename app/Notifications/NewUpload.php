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

class NewUpload extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * NewUpload Constructor.
     */
    public function __construct(public string $type, public Torrent $torrent)
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
        return [
            'title' => $this->torrent->user->username.' Has Uploaded A New Torrent',
            'body'  => \sprintf('%s, whom you are following has uploaded Torrent %s', $this->torrent->user->username, $this->torrent->name),
            'url'   => \sprintf('/torrents/%s', $this->torrent->id),
        ];
    }
}
