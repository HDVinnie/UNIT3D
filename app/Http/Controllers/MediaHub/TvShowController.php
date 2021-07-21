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
use App\Models\Tv;

class TvShowController extends Controller
{
    /**
     * Display All TV Shows.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('mediahub.tv.index');
    }

    /**
     * Show A TV Show.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $show = Tv::with(['seasons', 'genres', 'networks', 'companies'])->withCount('torrents')->findOrFail($id);

        return \view('mediahub.tv.show', [
            'show' => $show,
        ]);
    }
}
