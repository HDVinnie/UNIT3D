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
use App\Models\Audit;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\AuditControllerTest
 */
class AuditController extends Controller
{
    /**
     * Display All Audits.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $audits = Audit::with('user')->latest()->paginate(50);

        return \view('Staff.audit.index', ['audits' => $audits]);
    }

    /**
     * Delete A Audit.
     *
     *
     *@throws \Exception
     */
    public function destroy(Request $request, Audit $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $audit = Audit::findOrFail($id);

        \abort_unless($user->group->is_modo, 403);
        $audit->delete();

        return \redirect()->route('staff.audits.index')
            ->withSuccess('Audit Record Has Successfully Been Deleted');
    }
}
