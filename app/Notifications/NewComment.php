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

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewComment extends Notification
{
    use Queueable;

    /**
     * NewComment Constructor.
     */
    public function __construct(public string $type, public Comment $comment)
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
        if ($this->type === 'torrent') {
            if ($this->comment->anon == 0) {
                return [
                    'title' => 'New Torrent Comment Received',
                    'body'  => $this->comment->user->username.' has left you a comment on Torrent '.$this->comment->torrent->name,
                    'url'   => '/torrents/'.$this->comment->torrent->id,
                ];
            }

            return [
                'title' => 'New Torrent Comment Received',
                'body'  => 'Anonymous has left you a comment on Torrent '.$this->comment->torrent->name,
                'url'   => '/torrents/'.$this->comment->torrent->id,
            ];
        }
        if ($this->comment->anon == 0) {
            return [
                'title' => 'New Request Comment Received',
                'body'  => $this->comment->user->username.' has left you a comment on Torrent Request '.$this->comment->request->name,
                'url'   => '/requests/'.$this->comment->request->id,
            ];
        }

        return [
            'title' => 'New Request Comment Received',
            'body'  => 'Anonymous has left you a comment on Torrent Request '.$this->comment->request->name,
            'url'   => '/requests/'.$this->comment->request->id,
        ];
    }
}
