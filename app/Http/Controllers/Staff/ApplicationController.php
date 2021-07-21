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
use App\Mail\DenyApplication;
use App\Mail\InviteUser;
use App\Models\Application;
use App\Models\Invite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\ApplicationControllerTest
 */
class ApplicationController extends Controller
{
    /**
     * Display All Applications.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $applications = Application::withAnyStatus()
            ->with(['user', 'moderated', 'imageProofs', 'urlProofs'])
            ->latest()
            ->paginate(25);

        return \view('Staff.application.index', ['applications' => $applications]);
    }

    /**
     * Get A Application.
     */
    public function show(Application $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $application = Application::withAnyStatus()->with(['user', 'moderated', 'imageProofs', 'urlProofs'])->findOrFail($id);

        return \view('Staff.application.show', ['application' => $application]);
    }

    /**
     * Approve A Application.
     *
     *
     *@throws \Exception
     */
    public function approve(Request $request, Application $id): \Illuminate\Http\RedirectResponse
    {
        $application = Application::withAnyStatus()->findOrFail($id);

        if ($application->status !== 1) {
            $carbon = new Carbon();
            $user = $request->user();

            $code = Uuid::uuid4()->toString();
            $invite = new Invite();
            $invite->user_id = $user->id;
            $invite->email = $application->email;
            $invite->code = $code;
            $invite->expires_on = $carbon->copy()->addDays(\config('other.invite_expire'));
            $invite->custom = $request->input('approve');

            if (\config('email-blacklist.enabled') == true) {
                $v = \validator($request->all(), [
                    'email'   => 'required|string|email|max:70|blacklist|unique:users|unique:invites',
                    'approve' => 'required',
                ]);
            } else {
                $v = \validator($request->all(), [
                    'email'   => 'required|string|email|max:70|unique:users|unique:invites',
                    'approve' => 'required',
                ]);
            }

            if ($v->fails()) {
                return \redirect()->route('staff.applications.index')
                    ->withErrors($v->errors());
            }
            Mail::to($application->email)->send(new InviteUser($invite));
            $invite->save();
            $application->markApproved();

            return \redirect()->route('staff.applications.index')
                ->withSuccess('Application Approved');
        }

        return \redirect()->route('staff.applications.index')
                ->withErrors('Application Already Approved');
    }

    /**
     * Reject A Application.
     */
    public function reject(Request $request, Application $id): \Illuminate\Http\RedirectResponse
    {
        $application = Application::withAnyStatus()->findOrFail($id);

        if ($application->status !== 2) {
            $deniedMessage = $request->input('deny');
            $v = \validator($request->all(), [
                'deny' => 'required',
            ]);

            $application->markRejected();
            Mail::to($application->email)->send(new DenyApplication($deniedMessage));

            return \redirect()->route('staff.applications.index')
                ->withSuccess('Application Rejected');
        }

        return \redirect()->route('staff.applications.index')
            ->withErrors('Application Already Rejected');
    }
}
