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

namespace App\Http\Controllers\MediaHub;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Network;
use App\Models\Person;
use App\Models\Tv;

class HomeController extends Controller
{
    /**
     * Display Media Hubs.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $tv = Tv::count();
        $movies = Movie::count();
        $collections = Collection::count();
        $persons = Person::whereNotNull('still')->count();
        $genres = Genre::count();
        $networks = Network::count();
        $companies = Company::count();

        return \view('mediahub.index', [
            'tv'          => $tv,
            'movies'      => $movies,
            'collections' => $collections,
            'persons'     => $persons,
            'genres'      => $genres,
            'networks'    => $networks,
            'companies'   => $companies,
        ]);
    }
}
