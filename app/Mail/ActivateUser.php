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

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivateUser extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * ActivateUser constructor.
     *
     * @param \App\Models\User $user
     * @param                  $code
     */
    public function __construct(public User $user, public $code)
    {
    }

    public function build(): self
    {
        return $this->markdown('emails.activate')
            ->subject('Activation Required '.\config('other.title'));
    }
}
