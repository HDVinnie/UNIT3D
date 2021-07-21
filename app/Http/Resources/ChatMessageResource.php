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

namespace App\Http\Resources;

use andkab\LaravelJoyPixels\LaravelJoyPixels;
use App\Helpers\Bbcode;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array
     */
    public function toArray($request): array
    {
        $emojiOne = \app()->make(LaravelJoyPixels::class);

        $logger = null;
        $bbcode = new Bbcode();
        if ($this->user_id && $this->user_id == 1) {
            $logger = $bbcode->parse('<div class="align-left"><div class="chatTriggers">'.$this->message.'</div></div>');
            $logger = $emojiOne->toImage($logger);
            $logger = \str_replace('a href="/#', 'a trigger="bot" class="chatTrigger" href="/#', $logger);
        } else {
            $logger = $bbcode->parse('<div class="align-left">'.$this->message.'</div>');
            $logger = $emojiOne->toImage($logger);
        }
        $logger = \htmlspecialchars_decode($logger);

        return [
            'id'         => $this->id,
            'bot'        => new BotResource($this->whenLoaded('bot')),
            'user'       => new UserResource($this->whenLoaded('user')),
            'receiver'   => new UserResource($this->whenLoaded('receiver')),
            'chatroom'   => new ChatRoomResource($this->whenLoaded('chatroom')),
            'message'    => \clean($logger),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
