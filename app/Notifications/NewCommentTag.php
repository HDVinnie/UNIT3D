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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewCommentTag extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * NewCommentTag Constructor.
     */
    public function __construct(public string $type, public string $tagger, public Comment $comment)
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
            return [
                'title' => $this->tagger.' Has Tagged You In A Torrent Comment',
                'body'  => $this->tagger.' has tagged you in a Comment for Torrent '.$this->comment->torrent->name,
                'url'   => \sprintf('/torrents/%s', $this->comment->torrent->id),
            ];
        }

        if ($this->type === 'request') {
            return [
                'title' => $this->tagger.' Has Tagged You In A Request Comment',
                'body'  => $this->tagger.' has tagged you in a Comment for Request '.$this->comment->request->name,
                'url'   => \sprintf('/requests/%s', $this->comment->request->id),
            ];
        }

        return [
            'title' => $this->tagger.' Has Tagged You In An Article Comment',
            'body'  => $this->tagger.' has tagged you in a Comment for Article '.$this->comment->article->title,
            'url'   => \sprintf('/articles/%s', $this->comment->article->id),
        ];
    }
}
