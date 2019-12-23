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

namespace App\Mail;

use Illuminate\Contracts\Config\Repository;
use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class InviteUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \App\Models\Invite
     */
    public Invite $invite;
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $configRepository;

    /**
     * Create a new message instance.
     *
     * @param  Invite  $invite
     */
    public function __construct(Invite $invite, Repository $configRepository)
    {
        $this->invite = $invite;
        $this->configRepository = $configRepository;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->markdown('emails.invite')
            ->subject('Invite Received '.$this->configRepository->get('other.title'));
    }
}
