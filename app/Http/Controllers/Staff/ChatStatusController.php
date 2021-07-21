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
use App\Models\ChatStatus;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\ChatStatusControllerTest
 */
class ChatStatusController extends Controller
{
    /**
     * ChatController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Chat Management.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $chatstatuses = $this->chatRepository->statuses();

        return \view('Staff.chat.status.index', [
            'chatstatuses' => $chatstatuses,
        ]);
    }

    /**
     * Store A New Chat Status.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $chatstatus = new ChatStatus();
        $chatstatus->name = $request->input('name');
        $chatstatus->color = $request->input('color');
        $chatstatus->icon = $request->input('icon');

        $v = \validator($chatstatus->toArray(), [
            'name'  => 'required',
            'color' => 'required',
            'icon'  => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.statuses.index')
                ->withErrors($v->errors());
        }
        $chatstatus->save();

        return \redirect()->route('staff.statuses.index')
            ->withSuccess('Chat Status Successfully Added');
    }

    /**
     * Update A Chat Status.
     */
    public function update(Request $request, ChatStatus $id): \Illuminate\Http\RedirectResponse
    {
        $chatstatus = ChatStatus::findOrFail($id);
        $chatstatus->name = $request->input('name');
        $chatstatus->color = $request->input('color');
        $chatstatus->icon = $request->input('icon');

        $v = \validator($chatstatus->toArray(), [
            'name'  => 'required',
            'color' => 'required',
            'icon'  => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.statuses.index')
                ->withErrors($v->errors());
        }
        $chatstatus->save();

        return \redirect()->route('staff.statuses.index')
            ->withSuccess('Chat Status Successfully Modified');
    }

    /**
     * Delete A Chat Status.
     *
     * @param \App\Models\ChatStatus $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ChatStatus $id): \Illuminate\Http\RedirectResponse
    {
        $chatstatus = ChatStatus::findOrFail($id);
        $chatstatus->delete();

        return \redirect()->route('staff.statuses.index')
            ->withSuccess('Chat Status Successfully Deleted');
    }
}
