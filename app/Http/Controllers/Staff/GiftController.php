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
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\GiftControllerTest
 */
class GiftController extends Controller
{
    /**
     * Send Gift Form.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.gift.index');
    }

    /**
     * Send The Gift.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $staff = $request->user();

        $username = $request->input('username');
        $seedbonus = $request->input('seedbonus');
        $invites = $request->input('invites');
        $flTokens = $request->input('fl_tokens');

        $v = \validator($request->all(), [
            'username'  => 'required|exists:users,username|max:180',
            'seedbonus' => 'required|numeric|min:0',
            'invites'   => 'required|numeric|min:0',
            'fl_tokens' => 'required|numeric|min:0',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.gifts.index')
                ->withErrors($v->errors());
        }
        $recipient = User::where('username', '=', $username)->first();
        if (! $recipient) {
            return \redirect()->route('staff.gifts.index')
                ->withErrors('Unable To Find Specified User');
        }
        $recipient->seedbonus += $seedbonus;
        $recipient->invites += $invites;
        $recipient->fl_tokens += $flTokens;
        $recipient->save();
        // Send Private Message
        $privateMessage = new PrivateMessage();
        $privateMessage->sender_id = 1;
        $privateMessage->receiver_id = $recipient->id;
        $privateMessage->subject = 'You Have Received A System Generated Gift';
        $privateMessage->message = \sprintf('We just wanted to let you know that staff member, %s, has credited your account with %s Bonus Points, %s Invites and %s Freeleech Tokens.
                                [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]', $staff->username, $seedbonus, $invites, $flTokens);
        $privateMessage->save();

        return \redirect()->route('staff.gifts.index')
            ->withSuccess('Gift Sent');
    }
}
