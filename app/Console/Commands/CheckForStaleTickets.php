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

use App\Models\Ticket;
use Illuminate\Console\Command;

class CheckForStaleTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for tickets open longer than 3 days';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        Ticket::checkForStaleTickets();
    }
}
