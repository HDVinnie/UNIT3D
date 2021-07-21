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

use App\Helpers\EmailBlacklistUpdater;
use Illuminate\Console\Command;

class EmailBlacklistUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:email-blacklist-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cache for email domains blacklist.';

    /**
     * Execute the console command.
     *
     *@throws \JsonException
     *
     * @return void
     */
    public function handle(): void
    {
        $count = EmailBlacklistUpdater::update();

        if ($count === false) {
            $this->warn('No domains retrieved. Check the email.blacklist.source key for validation config.');

            return;
        }
        if ($count === 0) {
            $this->info('Advice: Blacklist was retrieved from source but 0 domains were listed.');

            return;
        }
        $this->info(\sprintf('%s domains retrieved. Cache updated. You are good to go.', $count));
    }
}
