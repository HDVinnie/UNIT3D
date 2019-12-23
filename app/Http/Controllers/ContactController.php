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

namespace App\Http\Controllers;

use Illuminate\Mail\Mailer;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Mail\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

final class ContactController extends Controller
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;
    /**
     * @var \Illuminate\Mail\Mailer
     */
    private $mailer;
    /**
     * @var \Illuminate\Routing\Redirector
     */
    private $redirector;
    public function __construct(Factory $viewFactory, Mailer $mailer, Redirector $redirector)
    {
        $this->viewFactory = $viewFactory;
        $this->mailer = $mailer;
        $this->redirector = $redirector;
    }
    /**
     * Contact Form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(): Factory
    {
        return $this->viewFactory->make('contact.index');
    }

    /**
     * Send A Contact Email To Owner/First User.
     *
     * @param  Request  $request
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Fetch owner account
        $user = User::where('id', '=', 3)->first();

        $input = $request->all();
        $this->mailer->to($user->email, $user->username)->send(new Contact($input));

        return $this->redirector->route('home.index')
            ->withSuccess('Your Message Was Successfully Sent');
    }
}
