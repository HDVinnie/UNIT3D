<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Staff;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\FailedLoginAttempt;

final class AuthenticationController extends Controller
{
    /**
     * Authentications Log.
     *
     * @return Factory|View
     */
    public function index()
    {
        $attempts = FailedLoginAttempt::latest()->paginate(25);

        return view('Staff.authentication.index', ['attempts' => $attempts]);
    }
}
