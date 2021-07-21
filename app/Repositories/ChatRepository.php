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

use App\Events\Chatter;
use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\Ping;
use App\Http\Resources\ChatMessageResource;
use App\Models\Bot;
use App\Models\Chatroom;
use App\Models\ChatStatus;
use App\Models\Message;
use App\Models\User;
use App\Models\UserAudible;
use App\Models\UserEcho;
use Illuminate\Support\Str;

class ChatRepository
{
    /**
     * ChatRepository Constructor.
     */
    public function __construct(private Message $message, private Chatroom $chatroom, private ChatStatus $chatStatus, private User $user, private Bot $bot, private UserEcho $userEcho, private UserAudible $userAudible)
    {
    }

    public function config()
    {
        return \config('chat');
    }

    public function bots(): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->bot->all();
    }

    public function echoes($userId): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->userEcho->with([
            'bot',
            'user',
            'target',
            'room',
        ])->where(function ($query) use ($userId) {
            $query->where('user_id', '=', $userId);
        })
            ->orderBy('id', 'asc')
            ->get();
    }

    public function audibles($userId): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->userAudible->with([
            'bot',
            'user',
            'target',
            'room',
        ])->where(function ($query) use ($userId) {
            $query->where('user_id', '=', $userId);
        })
            ->latest()
            ->get();
    }

    public function rooms(): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->chatroom->all();
    }

    public function roomFindOrFail($id)
    {
        return $this->chatroom->findOrFail($id);
    }

    public function ping($type, $id): bool
    {
        if ($type === 'room') {
            foreach (Chatroom::where('id', '>', 0)->get() as $room) {
                \broadcast(new Ping($room->id, $id));
            }
        }

        return true;
    }

    public function message($userId, $roomId, $message, $receiver = null, $bot = null)
    {
        if ($this->user->find($userId)->censor) {
            $message = $this->censorMessage($message);
        }

        $message = $this->htmlifyMessage($message);

        $message = $this->message->create([
            'user_id'     => $userId,
            'chatroom_id' => $roomId,
            'message'     => $message,
            'receiver_id' => $receiver,
            'bot_id'      => $bot,
        ]);

        $this->checkMessageLimits($roomId);

        \broadcast(new MessageSent($message));

        return $message;
    }

    public function botMessage($botId, $roomId, $message, $receiver = null): void
    {
        $user = $this->user->find($receiver);
        if ($user->censor) {
            $message = $this->censorMessage($message);
        }
        $message = $this->htmlifyMessage($message);
        $save = $this->message->create([
            'bot_id'      => $botId,
            'user_id'     => 1,
            'chatroom_id' => 0,
            'message'     => $message,
            'receiver_id' => $receiver,
        ]);

        $message = Message::with([
            'bot',
            'user.group',
            'user.chatStatus',
            'receiver.group',
            'receiver.chatStatus',
        ])->find($save->id);

        \event(new Chatter('new.bot', $receiver, new ChatMessageResource($message)));
        \event(new Chatter('new.ping', $receiver, ['type' => 'bot', 'id' => $botId]));
        $message->delete();
    }

    public function privateMessage($userId, $roomId, $message, $receiver = null, $bot = null, $ignore = null): \Illuminate\Database\Eloquent\Model | \Illuminate\Database\Eloquent\Collection | \Illuminate\Database\Eloquent\Builder | array | null
    {
        if ($this->user->find($userId)->censor) {
            $message = $this->censorMessage($message);
        }
        $message = $this->htmlifyMessage($message);

        $save = $this->message->create([
            'user_id'     => $userId,
            'chatroom_id' => 0,
            'message'     => $message,
            'receiver_id' => $receiver,
            'bot_id'      => $bot,
        ]);

        $message = Message::with([
            'bot',
            'user.group',
            'user.chatStatus',
            'receiver.group',
            'receiver.chatStatus',
        ])->find($save->id);

        if ($ignore != null) {
            \event(new Chatter('new.message', $userId, new ChatMessageResource($message)));
        }
        \event(new Chatter('new.message', $receiver, new ChatMessageResource($message)));

        if ($receiver != 1) {
            \event(new Chatter('new.ping', $receiver, ['type' => 'target', 'id' => $userId]));
        }

        return $message;
    }

    public function deleteMessage($id): Message
    {
        $message = $this->message->find($id);

        if ($message) {
            \broadcast(new MessageDeleted($message));

            return $message->delete();
        }
    }

    public function messages($roomId): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->message->with([
            'bot',
            'user.group',
            'chatroom',
            'user.chatStatus',
            'receiver.group',
            'receiver.chatStatus',
        ])->where(function ($query) use ($roomId) {
            $query->where('chatroom_id', '=', $roomId);
        })
            ->orderBy('id', 'desc')
            ->limit(\config('chat.message_limit'))
            ->get();
    }

    public function botMessages($senderId, $botId): \Illuminate\Database\Eloquent\Collection | array
    {
        $systemUserId = User::where('username', 'System')->firstOrFail()->id;

        return $this->message->with([
            'bot',
            'user.group',
            'chatroom',
            'user.chatStatus',
            'receiver.group',
            'receiver.chatStatus',
        ])->where(function ($query) use ($senderId, $systemUserId) {
            $query->whereRaw('(user_id = ? and receiver_id = ?)', [$senderId, $systemUserId])->orWhereRaw('(user_id = ? and receiver_id = ?)', [$systemUserId, $senderId]);
        })->where('bot_id', '=', $botId)
            ->orderBy('id', 'desc')
            ->limit(\config('chat.message_limit'))
            ->get();
    }

    public function privateMessages($senderId, $targetId): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->message->with([
            'bot',
            'user.group',
            'chatroom',
            'user.chatStatus',
            'receiver.group',
            'receiver.chatStatus',
        ])->where(function ($query) use ($senderId, $targetId) {
            $query->whereRaw('(user_id = ? and receiver_id = ?)', [$senderId, $targetId])->orWhereRaw('(user_id = ? and receiver_id = ?)', [$targetId, $senderId]);
        })
            ->orderBy('id', 'desc')
            ->limit(\config('chat.message_limit'))
            ->get();
    }

    public function checkMessageLimits($roomId): void
    {
        $messages = $this->messages($roomId)->toArray();
        $limit = \config('chat.message_limit');
        $count = \is_countable($messages) ? \count($messages) : 0;

        // Lets purge all old messages and keep the database to the limit settings
        if ($count > $limit) {
            for ($x = 1; $x <= $count - $limit; $x++) {
                $message = \array_pop($messages);
                echo $message['id']."\n";

                $message = $this->message->find($message['id']);

                if ($message->receiver_id === null) {
                    $message->delete();
                }
            }
        }
    }

    public function systemMessage($message, $bot = null): self
    {
        $systemUserId = User::where('username', 'System')->first()->id;

        if ($bot) {
            $this->message($systemUserId, $this->systemChatroom(), $message, null, $bot);
        } else {
            $systemBotId = Bot::where('command', 'systembot')->first()->id;

            $this->message($systemUserId, $this->systemChatroom(), $message, null, $systemBotId);
        }

        return $this;
    }

    public function systemChatroom($room = null)
    {
        $config = \config('chat.system_chatroom');

        if ($room !== null) {
            if ($room instanceof Chatroom) {
                $room = $room->id;
            } elseif (\is_int($room)) {
                $room = $this->chatroom->findOrFail($room)->id;
            } else {
                $room = $this->chatroom->whereName($room)->first()->id;
            }
        } elseif (\is_int($config)) {
            $room = $this->chatroom->findOrFail($config)->id;
        } elseif ($config instanceof Chatroom) {
            $room = $config->id;
        } else {
            $room = $this->chatroom->whereName($config)->first()->id;
        }

        return $room;
    }

    public function statuses(): \Illuminate\Database\Eloquent\Collection | array
    {
        return $this->chatStatus->all();
    }

    public function status($user): ChatStatus
    {
        if ($user instanceof User) {
            $status = $this->chatStatus->where('user_id', '=', $user->id)->first();
        }

        if (\is_int($user)) {
            $status = $this->chatStatus->where('user_id', '=', $user)->first();
        }

        return $status;
    }

    public function statusFindOrFail($id)
    {
        return $this->chatStatus->findOrFail($id);
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function censorMessage($message): string
    {
        foreach (\config('censor.redact') as $word) {
            if (\preg_match(\sprintf('/\b%s(?=[.,]|$|\s)/mi', $word), $message)) {
                $message = \str_replace($word, \sprintf("<span class='censor'>%s</span>", $word), $message);
            }
        }

        foreach (\config('censor.replace') as $word => $rword) {
            if (Str::contains($message, $word)) {
                $message = \str_replace($word, $rword, $message);
            }
        }

        return $message;
    }

    protected function htmlifyMessage($message)
    {
        // Soon

        return $message;
    }
}
