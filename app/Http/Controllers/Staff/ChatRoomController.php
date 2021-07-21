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

use App\Http\Controllers\Controller;
use App\Models\Chatroom;
use App\Models\User;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\ChatRoomControllerTest
 */
class ChatRoomController extends Controller
{
    /**
     * ChatController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Display All Chat Rooms.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $chatrooms = $this->chatRepository->rooms();

        return \view('Staff.chat.room.index', [
            'chatrooms'    => $chatrooms,
        ]);
    }

    /**
     * Store A New Chatroom.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $chatroom = new Chatroom();
        $chatroom->name = $request->input('name');

        $v = \validator($chatroom->toArray(), [
            'name' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.rooms.index')
                ->withErrors($v->errors());
        }
        $chatroom->save();

        return \redirect()->route('staff.rooms.index')
            ->withSuccess('Chatroom Successfully Added');
    }

    /**
     * Update A Chatroom.
     */
    public function update(Request $request, Chatroom $id): \Illuminate\Http\RedirectResponse
    {
        $chatroom = Chatroom::findOrFail($id);
        $chatroom->name = $request->input('name');

        $v = \validator($chatroom->toArray(), [
            'name' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.rooms.index')
                ->withErrors($v->errors());
        }
        $chatroom->save();

        return \redirect()->route('staff.rooms.index')
            ->withSuccess('Chatroom Successfully Modified');
    }

    /**
     * Delete A Chatroom.
     *
     * @param \App\Models\Chatroom $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Chatroom $id): \Illuminate\Http\RedirectResponse
    {
        $chatroom = Chatroom::findOrFail($id);
        $users = User::where('chatroom_id', '=', $id)->get();
        $default = Chatroom::where('name', '=', \config('chat.system_chatroom'))->pluck('id');
        foreach ($users as $user) {
            $user->chatroom_id = $default[0];
            $user->save();
        }
        $chatroom->delete();

        return \redirect()->route('staff.rooms.index')
            ->withSuccess('Chatroom Successfully Deleted');
    }
}
