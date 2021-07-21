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

namespace App\Console\Commands;

use App\Bots\IRCAnnounceBot;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class IrcMessage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'irc:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Messages an IRC Channel';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Messaging '.$this->argument('channel').': '.$this->argument('message'));
        $ircAnnounceBot = new IRCAnnounceBot();
        $ircAnnounceBot->message($this->argument('channel'), $this->argument('message'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['channel', InputArgument::REQUIRED, 'Channel that you would like to message'],
            ['message', InputArgument::REQUIRED, 'Message you would like to send'],
        ];
    }
}
