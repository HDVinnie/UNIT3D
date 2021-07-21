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

use App\Models\Movie;
use App\Models\Torrent;
use App\Models\Tv;
use Illuminate\Console\Command;

/**
 * @see \Tests\Todo\Unit\Console\Commands\FetchReleaseYearsTest
 */
class FetchReleaseYears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:release_years';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Release Years For Torrents In DB';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $appurl = \config('app.url');

        $torrents = Torrent::withAnyStatus()
            ->with(['category'])
            ->select(['id', 'name', 'category_id', 'tmdb', 'release_year'])
            ->whereNull('release_year')
            ->get();

        $withyear = Torrent::withAnyStatus()
            ->whereNotNull('release_year')
            ->count();

        $withoutyear = Torrent::withAnyStatus()
            ->whereNull('release_year')
            ->count();

        $this->alert(\sprintf('%s Torrents Already Have A Release Year Value!', $withyear));
        $this->alert(\sprintf('%s Torrents Are Missing A Release Year Value!', $withoutyear));

        foreach ($torrents as $torrent) {
            $meta = null;
            if ($torrent->category->tv_meta && $torrent->tmdb && $torrent->tmdb != 0) {
                $meta = Tv::where('id', '=', $torrent->tmdb)->first();
                if (\property_exists($meta, 'first_air_date') && $meta->first_air_date !== null && \substr($meta->first_air_date, 0, 4) > '1900') {
                    $torrent->release_year = \substr($meta->first_air_date, 0, 4);
                    $torrent->save();
                    $this->info(\sprintf('(%s) Release Year Fetched For Torrent %s ', $torrent->category->name, $torrent->name));
                } else {
                    $this->warn(\sprintf('(%s) No Release Year Found For Torrent %s %s/torrents/%s', $torrent->category->name, $torrent->name, $appurl, $torrent->id));
                }
            }
            if ($torrent->category->movie_meta && $torrent->tmdb && $torrent->tmdb != 0) {
                $meta = Movie::where('id', '=', $torrent->tmdb)->first();
                if (\property_exists($meta, 'release_date') && $meta->release_date !== null && \substr($meta->release_date, 0, 4) > '1900') {
                    $torrent->release_year = \substr($meta->release_date, 0, 4);
                    $torrent->save();
                    $this->info(\sprintf('(%s) Release Year Fetched For Torrent %s ', $torrent->category->name, $torrent->name));
                } else {
                    $this->warn(\sprintf('(%s) No Release Year Found For Torrent %s %s/torrents/%s', $torrent->category->name, $torrent->name, $appurl, $torrent->id));
                }
            }
        }
        $this->comment('Torrent Release Year Command Complete');
    }
}
