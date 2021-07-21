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

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * @see \Tests\Feature\Http\Controllers\BookmarkControllerTest
 */
class BookmarkController extends Controller
{
    /**
     * Display All Bookmarks.
     */
    public function index(Request $request, User $username): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        \abort_unless(($request->user()->id == $user->id), 403);

        return \view('bookmark.index', ['user' => $user]);
    }
}
