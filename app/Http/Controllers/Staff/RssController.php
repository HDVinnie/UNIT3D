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
use App\Models\Category;
use App\Models\Resolution;
use App\Models\Rss;
use App\Models\Type;
use App\Repositories\TorrentFacetedRepository;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\RssControllerTest
 */
class RssController extends Controller
{
    /**
     * RssController Constructor.
     */
    public function __construct(private TorrentFacetedRepository $torrentFacetedRepository)
    {
    }

    /**
     * Display a listing of the RSS resource.
     *
     * @param null $hash
     */
    public function index($hash = null): \Illuminate\Contracts\View\Factory | \Illuminate\Http\Response | \Illuminate\View\View
    {
        $publicRss = Rss::where('is_private', '=', 0)->orderBy('position', 'ASC')->get();

        return \view('Staff.rss.index', [
            'hash'       => $hash,
            'public_rss' => $publicRss,
        ]);
    }

    /**
     * Show the form for creating a new RSS resource.
     */
    public function create(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $torrentRepository = $this->torrentFacetedRepository;

        return \view('Staff.rss.create', [
            'torrent_repository' => $torrentRepository,
            'categories'         => Category::all()->sortBy('position'),
            'types'              => Type::all()->sortBy('position'),
            'resolutions'        => Resolution::all()->sortBy('position'),
            'user'               => $user, ]);
    }

    /**
     * Store a newly created RSS resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse | \Illuminate\Http\Response
    {
        $user = $request->user();

        $v = \validator($request->all(), [
            'name'        => 'required|min:3|max:255',
            'search'      => 'max:255',
            'description' => 'max:255',
            'uploader'    => 'max:255',
            'categories'  => 'sometimes|array|max:999',
            'types'       => 'sometimes|array|max:999',
            'resolutions' => 'sometimes|array|max:999',
            'genres'      => 'sometimes|array|max:999',
            'position'    => 'sometimes|integer|max:9999',
        ]);

        $params = $request->only(['type', 'name', 'position', 'search', 'description', 'uploader', 'imdb', 'tvdb', 'tmdb', 'mal', 'categories',
            'types', 'resolutions', 'genres', 'freeleech', 'doubleupload', 'featured', 'stream', 'highspeed', 'sd', 'internal', 'alive', 'dying', 'dead', ]);

        $error = null;
        $success = null;

        if ($v->passes()) {
            $rss = new Rss();
            $rss->name = $request->input('name');
            $rss->user_id = $user->id;
            $expected = $rss->expected_fields;
            $rss->json_torrent = \array_merge($expected, $params);
            $rss->is_private = 0;
            $rss->staff_id = $user->id;
            $rss->position = (int) $request->input('position');
            $rss->save();
            $success = 'Public RSS Feed Created';
        }
        if ($success === null) {
            $error = 'Unable To Process Request';
            if ($v->errors()) {
                $error = $v->errors();
            }

            return \redirect()->route('staff.rss.create')
                ->withErrors($error);
        }

        return \redirect()->route('staff.rss.index')
            ->withSuccess($success);
    }

    /**
     * Show the form for editing the specified RSS resource.
     */
    public function edit(Request $request, int $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $rss = Rss::where('is_private', '=', 0)->findOrFail($id);
        $torrentRepository = $this->torrentFacetedRepository;

        return \view('Staff.rss.edit', [
            'torrent_repository' => $torrentRepository,
            'categories'         => Category::all()->sortBy('position'),
            'types'              => Type::all()->sortBy('position'),
            'resolutions'        => Resolution::all()->sortBy('position'),
            'user'               => $user,
            'rss'                => $rss,
        ]);
    }

    /**
     * Update the specified RSS resource in storage.
     */
    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse | \Illuminate\Http\Response
    {
        $rss = Rss::where('is_private', '=', 0)->findOrFail($id);

        $v = \validator($request->all(), [
            'name'        => 'required|min:3|max:255',
            'search'      => 'max:255',
            'description' => 'max:255',
            'uploader'    => 'max:255',
            'categories'  => 'sometimes|array|max:999',
            'types'       => 'sometimes|array|max:999',
            'resolutions' => 'sometimes|array|max:999',
            'genres'      => 'sometimes|array|max:999',
            'position'    => 'sometimes|integer|max:9999',
        ]);

        $params = $request->only(['type', 'position', 'search', 'description', 'uploader', 'imdb', 'tvdb', 'tmdb', 'mal', 'categories',
            'types', 'resolutions', 'genres', 'freeleech', 'doubleupload', 'featured', 'stream', 'highspeed', 'sd', 'internal', 'alive', 'dying', 'dead', ]);

        $error = null;
        $success = null;
        $redirect = null;

        if ($v->passes()) {
            $expected = $rss->expected_fields;
            $push = \array_merge($expected, $params);
            $rss->json_torrent = \array_merge($rss->json_torrent, $push);
            $rss->is_private = 0;
            $rss->name = $request->input('name');
            $rss->position = (int) $request->input('position');
            $rss->save();
            $success = 'Public RSS Feed Updated';
        }
        if ($success === null) {
            $error = 'Unable To Process Request';
            if ($v->errors()) {
                $error = $v->errors();
            }

            return \redirect()->route('staff.rss.edit', ['id' => $id])
                ->withErrors($error);
        }

        return \redirect()->route('staff.rss.index')
            ->withSuccess($success);
    }

    /**
     * Remove the specified RSS resource from storage.
     *
     * @param int $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        $rss = Rss::where('is_private', '=', 0)->findOrFail($id);
        $rss->delete();

        return \redirect()->route('staff.rss.index')
            ->withSuccess('RSS Feed Deleted!');
    }
}
