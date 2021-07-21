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

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Chatter implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $echoes;

    public $message;

    public $ping;

    public $audibles;

    /**
     * Chatter Constructor.
     *
     * @param $type
     * @param $target
     * @param $payload
     */
    public function __construct(public $type, public $target, $payload)
    {
        if ($type === 'echo') {
            $this->echoes = $payload;
        } elseif ($type === 'audible') {
            $this->audibles = $payload;
        } elseif ($type === 'new.message') {
            $this->message = $payload;
        } elseif ($type === 'new.bot') {
            $this->message = $payload;
        } elseif ($type === 'new.ping') {
            $this->ping = $payload;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chatter.'.$this->target);
    }
}
