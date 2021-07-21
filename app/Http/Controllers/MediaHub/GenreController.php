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
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * Display All Genres.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $genres = Genre::paginate(25);

        return \view('mediahub.genre.index', ['genres' => $genres]);
    }

    /**
     * Show A Genre.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $genre = Genre::withCount(['tv', 'movie'])->findOrFail($id);
        $shows = $genre->tv()->orderBy('name', 'asc')->paginate(25);
        $movies = $genre->movie()->orderBy('title', 'asc')->paginate(25);

        return \view('mediahub.genre.show', [
            'genre'  => $genre,
            'shows'  => $shows,
            'movies' => $movies,
        ]);
    }
}
