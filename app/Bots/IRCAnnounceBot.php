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

namespace App\Bots;

class IRCAnnounceBot
{
    /**
     * @var mixed
     */
    public mixed $server;

    /**
     * @var mixed
     */
    public mixed $port;

    /**
     * @var mixed
     */
    public mixed $hostname;

    /**
     * @var mixed
     */
    public mixed $nickservpass;

    /**
     * @var mixed
     */
    public mixed $joinchannel;

    protected $socket = null;

    private mixed $channel;

    private mixed $username;

    private bool $registered = false;

    public function __construct()
    {
        $this->username = \config('irc-bot.username');
        $this->channel = \config('irc-bot.channel');
        $this->server = \config('irc-bot.server');
        $this->port = \config('irc-bot.port');
        $this->hostname = \config('irc-bot.hostname');
        $this->nickservpass = \config('irc-bot.nickservpass');
        $this->joinchannel = \config('irc-bot.joinchannel');
        $this->socket = \fsockopen($this->server, $this->port);

        $this->send_data(\sprintf('NICK %s', $this->username));
        $this->send_data(\sprintf('USER %s %s %s %s', $this->username, $this->hostname, $this->server, $this->username));

        $this->connect();
    }

    public function __destruct()
    {
        if ($this->socket) {
            \fclose($this->socket);
        }
    }

    private function connect(): void
    {
        while ($data = \fgets($this->socket)) {
            \flush();
            $ex = \explode(' ', $data);

            if ($ex[0] === 'PING') {
                $this->send_data('PONG '.$ex[1]);
                if ($this->nickservpass) {
                    $this->send_data(\sprintf('NICKSERV IDENTIFY %s', $this->nickservpass));
                }

                return;
            }
        }
    }

    private function send_data($data): void
    {
        \fwrite($this->socket, \sprintf('%s', $data));
    }

    private function say($channel, $string): void
    {
        $this->send_data(\sprintf('PRIVMSG %s %s', $channel, $string));
    }

    private function join($channel): void
    {
        $this->send_data(\sprintf('JOIN %s', $channel));
    }

    public function message($channel, $message): void
    {
        // Messages an specific IRC Channel
        if ($this->joinchannel && \preg_match('/#(\w*[a-zA-Z_0-9]+\w*)/', $channel)) {
            $this->join($channel);
        }

        $this->say($channel, $message);
    }
}
