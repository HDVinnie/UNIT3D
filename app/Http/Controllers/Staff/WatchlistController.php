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
use App\Models\Watchlist;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\WatchlistControllerTest
 */
class WatchlistController extends Controller
{
    /**
     * Watchlist.
     */
    final public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.watchlist.index');
    }

    /**
     * Store A New Watched User.
     */
    final public function store(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $watchedUser = new Watchlist();
        $watchedUser->user_id = $id;
        $watchedUser->staff_id = $request->user()->id;
        $watchedUser->message = $request->input('message');

        $v = \validator($watchedUser->toArray(), [
            'user_id'  => 'required|exists:users,id',
            'staff_id' => 'required|exists:users,id',
            'message'  => 'required|min:3',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.watchlist.index')
                ->withErrors($v->errors());
        }
        $watchedUser->save();

        return \redirect()->route('staff.watchlist.index')
            ->withSuccess('User Successfully Being Watched');
    }

    /**
     * Delete A Watched User.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    final public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $watchedUser = Watchlist::findOrFail($id);
        $watchedUser->delete();

        return \redirect()->route('staff.watchlist.index')
            ->withSuccess('Successfully Stopped Watching User');
    }
}
