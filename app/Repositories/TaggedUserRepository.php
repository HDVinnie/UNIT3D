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

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\NewCommentTag;
use App\Notifications\NewPostTag;

class TaggedUserRepository
{
    /**
     * Enables various debugging options:.
     *
     * 1. Allows you to tag yourself while testing and debugging
     *
     * @var bool
     */
    protected bool $debug = false;

    /**
     * @var string
     */
    protected string $regex = '/@[a-zA-Z0-9-_]+/m';

    /**
     * TaggedUserRepository Constructor.
     */
    public function __construct(private User $user, private PrivateMessage $privateMessage)
    {
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    public function getTags($content): mixed
    {
        \preg_match_all($this->regex, $content, $tagged);

        return $tagged[0];
    }

    /**
     * @param $content
     *
     * @return bool
     */
    public function hasTags($content): bool
    {
        return $this->getTags($content) > 0;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public function contains($haystack, $needle): bool
    {
        return \collect($this->getTags($haystack))->contains($needle);
    }

    public function messageTaggedCommentUsers(string $type, string $content, User $user, $alias, Comment $comment): bool
    {
        foreach ($this->getTags($content) as $tag) {
            $taggedUser = $this->user->where('username', \str_replace('@', '', $tag))->first();
            $this->messageCommentUsers($type, $taggedUser, $user, $alias, $comment);
        }

        return true;
    }

    public function messageCommentUsers($type, $users, $sender, $alias, Comment $comment): bool
    {
        // Array of User objects
        if (\is_iterable($users)) {
            // we only want unique users from the collection
            $users = \is_array($users) ? \collect($users)->unique() : $users->unique();

            foreach ($users as $user) {
                if ($this->validate($user) && $user->acceptsNotification($sender, $user, 'mention', 'show_mention_'.$type.'_comment')) {
                    $user->notify(new NewCommentTag($type, $alias, $comment));
                }
            }

            return true;
        }

        // A single User object

        if ($this->validate($users) && $users->acceptsNotification($sender, $users, 'mention', 'show_mention_'.$type.'_comment')) {
            $users->notify(new NewCommentTag($type, $alias, $comment));
        }

        return true;
    }

    public function messageTaggedPostUsers(string $type, string $content, User $user, $alias, Post $post): bool
    {
        foreach ($this->getTags($content) as $tag) {
            $taggedUser = $this->user->where('username', \str_replace('@', '', $tag))->first();
            $this->messagePostUsers($type, $taggedUser, $user, $alias, $post);
        }

        return true;
    }

    public function messagePostUsers($type, $users, $sender, $alias, Post $post): bool
    {
        // Array of User objects
        if (\is_iterable($users)) {
            // we only want unique users from the collection
            $users = \is_array($users) ? \collect($users)->unique() : $users->unique();

            foreach ($users as $user) {
                if ($this->validate($user) && $user->acceptsNotification($sender, $user, 'mention', 'show_mention_'.$type.'_post')) {
                    $user->notify(new NewPostTag($type, $alias, $post));
                }
            }

            return true;
        }

        // A single User object
        if ($this->validate($users) && $users->acceptsNotification($sender, $users, 'mention', 'show_mention_'.$type.'_post')) {
            $users->notify(new NewPostTag($type, $alias, $post));
        }

        return true;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    protected function validate($user): bool
    {
        return \is_object($user);
    }
}
