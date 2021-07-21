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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\CommandControllerTest
 */
class CommandController extends Controller
{
    /**
     * Display All Commands.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        return \view('Staff.command.index');
    }

    /**
     * Bring Site Into Maintenance Mode.
     */
    public function maintanceEnable(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('down');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Bring Site Out Of Maintenance Mode.
     */
    public function maintanceDisable(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('up');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Clear Site Cache.
     */
    public function clearCache(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('cache:clear');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Clear Site View Cache.
     */
    public function clearView(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('view:clear');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Clear Site Routes Cache.
     */
    public function clearRoute(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('route:clear');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Clear Site Config Cache.
     */
    public function clearConfig(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('config:clear');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Clear All Site Cache At Once.
     */
    public function clearAllCache(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('clear:all_cache');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Set All Site Cache At Once.
     */
    public function setAllCache(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('set:all_cache');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(Artisan::output()));
    }

    /**
     * Send Test Email To Test Email Configuration.
     */
    public function testEmail(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_owner, 403);

        Artisan::call('test:email');

        return \redirect()->route('staff.commands.index')
            ->withInfo(\trim(\str_replace(["\r", "\n", '*'], '', Artisan::output())));
    }
}
