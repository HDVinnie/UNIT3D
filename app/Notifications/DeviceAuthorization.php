<?php
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

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class DeviceAuthorization extends Notification
{
    use Queueable;

    /**
     * The verify token.
     *
     * @var string
     */
    protected $verifyToken;

    /**
     * The IP address the notification was request from.
     *
     * @var string
     */
    protected $ipAddress;

    /**
     * The browser name the notification was requested from.
     *
     * @var string
     */
    protected $browser;

    /**
     * The platform name the notification was requested from.
     *
     * @var string
     */
    protected $platform;

    /**
     * Create a new notification instance.
     *
     * @param $verifyToken
     * @param $ipAddress
     * @param $browser
     * @param $platform
     */
    public function __construct($verifyToken, $ipAddress, $browser, $platform)
    {
        $this->verifyToken = $verifyToken;

        $this->ipAddress = $ipAddress;

        $this->browser = $browser;

        $this->platform = $platform;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('New Device Confirmation')
            ->line('You recently attempted to sign into your account from a new device. As a security measure, we require additional confirmation before allowing access to your account:')
            ->line(new HtmlString('<strong>IP Address: '.$this->ipAddress.'</strong>'))
            ->line(new HtmlString('<strong>Browser: '.$this->browser.'('.$this->platform.')</strong>'))
            ->line('Note thate you will need to do this on the same device and in the same browser as you were using.')
            ->action('Verify Device', route('device.verify', [$this->verifyToken]))
            ->line('Thanks for helping us to keep your account secure!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message'  => 'You requested to authenticate a new device.',
            'url'      => route('device.verify', [$this->verifyToken]),
            'ip'       => $this->ipAddress,
            'browser'  => $this->browser,
            'platform' => $this->platform,
        ];
    }
}
