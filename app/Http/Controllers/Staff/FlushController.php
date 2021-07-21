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

namespace App\Http\Controllers\Staff;

use App\Events\MessageDeleted;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Message;
use App\Models\Peer;
use App\Repositories\ChatRepository;
use Carbon\Carbon;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\FlushControllerTest
 */
class FlushController extends Controller
{
    /**
     * FlushController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Flsuh All Old Peers From Database.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function peers(): \Illuminate\Http\RedirectResponse
    {
        $carbon = new Carbon();
        $peers = Peer::select(['id', 'info_hash', 'user_id', 'updated_at'])->where('updated_at', '<', $carbon->copy()->subHours(2)->toDateTimeString())->get();

        foreach ($peers as $peer) {
            $history = History::where('info_hash', '=', $peer->info_hash)->where('user_id', '=', $peer->user_id)->first();
            if ($history) {
                $history->active = false;
                $history->save();
            }
            $peer->delete();
        }

        return \redirect()->route('staff.dashboard.index')
            ->withSuccess('Ghost Peers Have Been Flushed');
    }

    /**
     * Flush All Chat Messages.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function chat(): \Illuminate\Http\RedirectResponse
    {
        foreach (Message::all() as $message) {
            \broadcast(new MessageDeleted($message));
            $message->delete();
        }

        $this->chatRepository->systemMessage(
            'Chatbox Has Been Flushed! :broom:'
        );

        return \redirect()->route('staff.dashboard.index')
            ->withSuccess('Chatbox Has Been Flushed');
    }
}
