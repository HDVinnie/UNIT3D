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

use App\Models\Page;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\PageControllerTest
 */
class PageController extends Controller
{
    /**
     * Display All Pages.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $pages = Page::all();

        return \view('page.index', ['pages' => $pages]);
    }

    /**
     * Show A Page.
     */
    public function show(Page $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $page = Page::findOrFail($id);

        return \view('page.page', ['page' => $page]);
    }

    /**
     * Show Staff Page.
     */
    public function staff(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $staff = DB::table('users')->leftJoin('groups', 'users.group_id', '=', 'groups.id')->select(['users.id', 'users.title', 'users.username', 'groups.name', 'groups.color', 'groups.icon'])->where('groups.is_admin', 1)->orWhere('groups.is_modo', 1)->get();

        return \view('page.staff', ['staff' => $staff]);
    }

    /**
     * Show Internals Page.
     */
    public function internal(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $internal = DB::table('users')->leftJoin('groups', 'users.group_id', '=', 'groups.id')->select(['users.id', 'users.title', 'users.username', 'groups.name', 'groups.color', 'groups.icon'])->where('groups.is_internal', 1)->get();

        return \view('page.internal', ['internal' => $internal]);
    }

    /**
     * Show Blacklist Page.
     */
    public function blacklist(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $clients = \config('client-blacklist.clients', []);

        return \view('page.blacklist', ['clients' => $clients]);
    }

    /**
     * Show About Us Page.
     */
    public function about(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('page.aboutus');
    }
}
