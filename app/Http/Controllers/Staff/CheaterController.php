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

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\CheaterControllerTest
 */
class CheaterController extends Controller
{
    /**
     * Possible Ghost Leech Cheaters.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $cheaters = History::with('user')
            ->select(['*'])
            ->join(
                DB::raw('(SELECT MAX(id) AS id FROM history GROUP BY history.user_id) AS unique_history'),
                function ($join) {
                    $join->on('history.id', '=', 'unique_history.id');
                }
            )
            ->where('seeder', '=', 0)
            ->where('active', '=', 0)
            ->where('seedtime', '=', 0)
            ->where('actual_downloaded', '=', 0)
            ->where('actual_uploaded', '=', 0)
            ->whereNull('completed_at')
            ->latest()
            ->paginate(25);

        return \view('Staff.cheater.index', ['cheaters' => $cheaters]);
    }
}
