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

use App\Mail\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * @see \Tests\Feature\Http\Controllers\ContactControllerTest
 */
class ContactController extends Controller
{
    /**
     * Contact Form.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('contact.index');
    }

    /**
     * Send A Contact Email To Owner/First User.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Fetch owner account
        $user = User::where('username', \config('unit3d.owner-username'))->first();

        $input = $request->all();
        Mail::to($user->email)->send(new Contact($input));

        return \redirect()->route('home.index')
            ->withSuccess('Your Message Was Successfully Sent');
    }
}
