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

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use App\Models\Resolution;
use App\Models\Rss;
use App\Models\Torrent;
use App\Models\Type;
use App\Models\User;
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
    public function index(Request $request, $hash = null): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();

        $publicRss = Rss::where('is_private', '=', 0)->orderBy('position', 'ASC')->get();
        $privateRss = Rss::where('is_private', '=', 1)->where('user_id', '=', $user->id)->latest()->get();

        return \view('rss.index', [
            'hash'        => $hash,
            'public_rss'  => $publicRss,
            'private_rss' => $privateRss,
            'user'        => $user,
        ]);
    }

    /**
     * Show the form for creating a new RSS resource.
     */
    public function create(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $torrentRepository = $this->torrentFacetedRepository;

        return \view('rss.create', [
            'torrent_repository' => $torrentRepository,
            'categories'         => Category::all()->sortBy('position'),
            'types'              => Type::all()->sortBy('position'),
            'resolutions'        => Resolution::all()->sortBy('position'),
            'user'               => $user,
        ]);
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

        $params = $request->only([
            'name',
            'search',
            'description',
            'uploader',
            'imdb',
            'tvdb',
            'tmdb',
            'mal',
            'categories',
            'types',
            'resolutions',
            'genres',
            'freeleech',
            'doubleupload',
            'featured',
            'stream',
            'highspeed',
            'sd',
            'internal',
            'alive',
            'dying',
            'dead',
        ]);

        $error = null;
        $success = null;

        if ($v->passes()) {
            $rss = new Rss();
            $rss->name = $request->input('name');
            $rss->user_id = $user->id;
            $expected = $rss->expected_fields;
            $rss->json_torrent = \array_merge($expected, $params);
            $rss->is_private = 1;
            $rss->save();
            $success = 'Private RSS Feed Created';
        }
        if ($success === null) {
            $error = 'Unable To Process Request';
            if ($v->errors()) {
                $error = $v->errors();
            }

            return \redirect()->route('rss.create')
                ->withErrors($error);
        }

        return \redirect()->route('rss.index', ['hash' => 'private'])
            ->withSuccess($success);
    }

    /**
     * Display the specified RSS resource.
     *
     * @param int    $id
     * @param string $rsskey
     *
     *@throws \Exception
     *
     * @return array
     */
    public function show(int $id, string $rsskey): array
    {
        $user = User::where('rsskey', '=', $rsskey)->firstOrFail();

        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));

        if ($user->group->id == $bannedGroup[0]) {
            \abort(404);
        }
        if ($user->group->id == $disabledGroup[0]) {
            \abort(404);
        }
        if ($user->active == 0) {
            \abort(404);
        }

        $rss = Rss::where('id', '=', $id)->whereRaw('(user_id = ? OR is_private != ?)', [$user->id, 1])->firstOrFail();

        $search = $rss->object_torrent->search;
        $description = $rss->object_torrent->description;
        $uploader = $rss->object_torrent->uploader;
        $imdb = $rss->object_torrent->imdb;
        $tvdb = $rss->object_torrent->tvdb;
        $tmdb = $rss->object_torrent->tmdb;
        $mal = $rss->object_torrent->mal;
        $categories = $rss->object_torrent->categories;
        $types = $rss->object_torrent->types;
        $resolutions = $rss->object_torrent->resolutions;
        $genres = $rss->object_torrent->genres;
        $freeleech = $rss->object_torrent->freeleech;
        $doubleupload = $rss->object_torrent->doubleupload;
        $featured = $rss->object_torrent->featured;
        $stream = $rss->object_torrent->stream;
        $highspeed = $rss->object_torrent->highspeed;
        $sd = $rss->object_torrent->sd;
        $internal = $rss->object_torrent->internal;
        $alive = $rss->object_torrent->alive;
        $dying = $rss->object_torrent->dying;
        $dead = $rss->object_torrent->dead;

        $terms = \explode(' ', $search);
        $search = '';
        foreach ($terms as $term) {
            $search .= '%'.$term.'%';
        }

        $usernames = \explode(' ', $uploader);
        $uploader = '';
        foreach ($usernames as $username) {
            $uploader .= '%'.$username.'%';
        }

        $keywords = \explode(' ', $description);
        $description = '';
        foreach ($keywords as $keyword) {
            $description .= '%'.$keyword.'%';
        }

        $builder = Torrent::with(['user', 'category', 'type', 'resolution']);

        if ($rss->object_torrent->search) {
            $builder->where(function ($query) use ($search) {
                $query->where('name', 'like', $search);
            });
        }

        if ($rss->object_torrent->description) {
            $builder->where(function ($query) use ($description) {
                $query->where('description', 'like', $description)->orWhere('mediainfo', 'like', $description);
            });
        }

        if ($rss->object_torrent->uploader && $rss->object_torrent->uploader != null) {
            $match = User::where('username', 'like', $uploader)->first();
            if (null === $match) {
                return ['result' => [], 'count' => 0];
            }
            $builder->where('user_id', '=', $match->id)->where('anon', '=', 0);
        }

        if ($rss->object_torrent->imdb && $rss->object_torrent->imdb != null) {
            $builder->where('imdb', '=', $imdb);
        }

        if ($rss->object_torrent->tvdb && $rss->object_torrent->tvdb != null) {
            $builder->where('tvdb', '=', $tvdb);
        }

        if ($rss->object_torrent->tmdb && $rss->object_torrent->tmdb != null) {
            $builder->where('tmdb', '=', $tmdb);
        }

        if ($rss->object_torrent->mal && $rss->object_torrent->mal != null) {
            $builder->where('mal', '=', $mal);
        }

        if ($rss->object_torrent->categories && \is_array($rss->object_torrent->categories)) {
            $builder->whereIn('category_id', $categories);
        }

        if ($rss->object_torrent->types && \is_array($rss->object_torrent->types)) {
            $builder->whereIn('type_id', $types);
        }

        if ($rss->object_torrent->resolutions && \is_array($rss->object_torrent->resolutions)) {
            $builder->whereIn('resolution_id', $resolutions);
        }

        if ($rss->object_torrent->freeleech && $rss->object_torrent->freeleech != null) {
            $builder->where('free', '=', $freeleech);
        }

        if ($rss->object_torrent->doubleupload && $rss->object_torrent->doubleupload != null) {
            $builder->where('doubleup', '=', $doubleupload);
        }

        if ($rss->object_torrent->featured && $rss->object_torrent->featured != null) {
            $builder->where('featured', '=', $featured);
        }

        if ($rss->object_torrent->stream && $rss->object_torrent->stream != null) {
            $builder->where('stream', '=', $stream);
        }

        if ($rss->object_torrent->highspeed && $rss->object_torrent->highspeed != null) {
            $builder->where('highspeed', '=', $highspeed);
        }

        if ($rss->object_torrent->sd && $rss->object_torrent->sd != null) {
            $builder->where('sd', '=', $sd);
        }

        if ($rss->object_torrent->internal && $rss->object_torrent->internal != null) {
            $builder->where('internal', '=', $internal);
        }

        if ($rss->object_torrent->alive && $rss->object_torrent->alive != null) {
            $builder->where('seeders', '>=', $alive);
        }

        if ($rss->object_torrent->dying && $rss->object_torrent->dying != null) {
            $builder->where('seeders', '=', $dying)->where('times_completed', '>=', 3);
        }

        if ($rss->object_torrent->dead && $rss->object_torrent->dead != null) {
            $builder->where('seeders', '=', $dead);
        }

        $torrents = $builder->latest()->take(50)->get();

        return \response()->view('rss.show', ['torrents' => $torrents, 'user' => $user, 'rss' => $rss])->header('Content-Type', 'text/xml');
    }

    /**
     * Show the form for editing the specified RSS resource.
     */
    public function edit(Request $request, int $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $rss = Rss::where('is_private', '=', 1)->findOrFail($id);
        \abort_unless($user->group->is_modo || $user->id === $rss->user_id, 403);
        $torrentRepository = $this->torrentFacetedRepository;

        return \view('rss.edit', [
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
        $rss = Rss::where('is_private', '=', 1)->findOrFail($id);

        $v = \validator($request->all(), [
            'search'      => 'max:255',
            'description' => 'max:255',
            'uploader'    => 'max:255',
            'categories'  => 'sometimes|array|max:999',
            'types'       => 'sometimes|array|max:999',
            'resolutions' => 'sometimes|array|max:999',
            'genres'      => 'sometimes|array|max:999',
            'position'    => 'sometimes|integer|max:9999',
        ]);

        $params = $request->only([
            'search',
            'description',
            'uploader',
            'imdb',
            'tvdb',
            'tmdb',
            'mal',
            'categories',
            'types',
            'resolutions',
            'genres',
            'freeleech',
            'doubleupload',
            'featured',
            'stream',
            'highspeed',
            'sd',
            'internal',
            'alive',
            'dying',
            'dead',
        ]);

        $error = null;
        $success = null;
        $redirect = null;
        if ($v->passes()) {
            $expected = $rss->expected_fields;
            $push = \array_merge($expected, $params);
            $rss->json_torrent = \array_merge($rss->json_torrent, $push);
            $rss->is_private = 1;
            $rss->save();
            $success = 'Private RSS Feed Updated';
        }
        if ($success === null) {
            $error = 'Unable To Process Request';
            if ($v->errors()) {
                $error = $v->errors();
            }

            return \redirect()->route('rss.edit', ['id' => $id])
                ->withErrors($error);
        }

        return \redirect()->route('rss.index', ['hash' => 'private'])
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
        $rss = Rss::where('is_private', '=', 1)->findOrFail($id);
        $rss->delete();

        return \redirect()->route('rss.index', ['hash' => 'private'])
            ->withSuccess('RSS Feed Deleted!');
    }
}
