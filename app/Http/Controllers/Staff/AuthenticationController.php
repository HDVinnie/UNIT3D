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
use App\Models\FailedLoginAttempt;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\AuthenticationControllerTest
 */
class AuthenticationController extends Controller
{
    /**
     * Authentications Log.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $attempts = FailedLoginAttempt::latest()->paginate(25);

        return \view('Staff.authentication.index', ['attempts' => $attempts]);
    }
}
