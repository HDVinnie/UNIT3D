<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie, singularity43
 */

namespace App\Notifications;

use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class NewUpload extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \App\Models\Torrent
     */
    public $torrent;

    /**
     * Create a new notification instance.
     *
     * @param string  $type
     * @param Torrent $torrent
     *
     * @return void
     */
    public function __construct(string $type, Torrent $torrent)
    {
        $this->type = $type;
        $this->torrent = $torrent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return string[]
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return string[]
     */
    public function toArray($notifiable): array
    {
        $appurl = config('app.url');

        return [
            'title' => $this->torrent->user->username.' Has Uploaded A New Torrent',
            'body'  => sprintf('%s, whom you are following has uploaded Torrent %s', $this->torrent->user->username, $this->torrent->name),
            'url'   => sprintf('/torrents/%s', $this->torrent->id),
        ];
    }
}
