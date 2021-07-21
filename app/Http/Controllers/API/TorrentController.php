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

namespace App\Http\Controllers\API;

use App\Helpers\Bencode;
use App\Helpers\MediaInfo;
use App\Helpers\TorrentHelper;
use App\Helpers\TorrentTools;
use App\Http\Resources\TorrentResource;
use App\Http\Resources\TorrentsResource;
use App\Models\Category;
use App\Models\FeaturedTorrent;
use App\Models\Keyword;
use App\Models\PlaylistTorrent;
use App\Models\Torrent;
use App\Models\TorrentFile;
use App\Models\User;
use App\Repositories\ChatRepository;
use App\Services\Tmdb\TMDBScraper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\TorrentControllerTest
 */
class TorrentController extends BaseController
{
    /**
     * TorrentController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return TorrentsResource
     */
    public function index(): TorrentsResource
    {
        return new TorrentsResource(Torrent::with(['category', 'type', 'resolution'])
            ->orderBy('sticky', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->paginate(25));
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse | \Illuminate\Http\Response
    {
        $user = $request->user();
        $requestFile = $request->file('torrent');
        if (! $request->hasFile('torrent')) {
            return $this->sendError('Validation Error.', 'You Must Provide A Torrent File For Upload!');
        }

        if ($requestFile->getError() !== 0 || $requestFile->getClientOriginalExtension() !== 'torrent') {
            return $this->sendError('Validation Error.', 'You Must Provide A Valid Torrent File For Upload!');
        }

        // Deplace and decode the torrent temporarily
        $decodedTorrent = TorrentTools::normalizeTorrent($requestFile);
        $infohash = Bencode::get_infohash($decodedTorrent);

        try {
            $meta = Bencode::get_meta($decodedTorrent);
        } catch (\Exception) {
            return $this->sendError('Validation Error.', 'You Must Provide A Valid Torrent File For Upload!');
        }

        foreach (TorrentTools::getFilenameArray($decodedTorrent) as $name) {
            if (! TorrentTools::isValidFilename($name)) {
                return $this->sendError('Validation Error.', 'Invalid Filenames In Torrent Files!');
            }
        }

        $fileName = \sprintf('%s.torrent', \uniqid('', true)); // Generate a unique name
        Storage::disk('torrents')->put($fileName, Bencode::bencode($decodedTorrent));

        // Find the right category
        $category = Category::withCount('torrents')->findOrFail($request->input('category_id'));

        // Create the torrent (DB)
        $torrent = \app()->make(Torrent::class);
        $torrent->name = $request->input('name');
        $torrent->slug = Str::slug($torrent->name);
        $torrent->description = $request->input('description');
        $torrent->mediainfo = self::anonymizeMediainfo($request->input('mediainfo'));
        $torrent->bdinfo = $request->input('bdinfo');
        $torrent->info_hash = $infohash;
        $torrent->file_name = $fileName;
        $torrent->num_file = $meta['count'];
        $torrent->announce = $decodedTorrent['announce'];
        $torrent->size = $meta['size'];
        $torrent->nfo = ($request->hasFile('nfo')) ? TorrentTools::getNfo($request->file('nfo')) : '';
        $torrent->category_id = $category->id;
        $torrent->type_id = $request->input('type_id');
        $torrent->resolution_id = $request->input('resolution_id');
        $torrent->user_id = $user->id;
        $torrent->imdb = $request->input('imdb');
        $torrent->tvdb = $request->input('tvdb');
        $torrent->tmdb = $request->input('tmdb');
        $torrent->mal = $request->input('mal');
        $torrent->igdb = $request->input('igdb');
        $torrent->anon = $request->input('anonymous');
        $torrent->stream = $request->input('stream');
        $torrent->sd = $request->input('sd');
        $torrent->personal_release = $request->input('personal_release') ?? 0;
        $torrent->internal = $user->group->is_modo || $user->group->is_internal ? $request->input('internal') : 0;
        $torrent->featured = $user->group->is_modo || $user->group->is_internal ? $request->input('featured') : 0;
        $torrent->doubleup = $user->group->is_modo || $user->group->is_internal ? $request->input('doubleup') : 0;
        $torrent->free = $user->group->is_modo || $user->group->is_internal ? $request->input('free') : 0;
        $torrent->sticky = $user->group->is_modo || $user->group->is_internal ? $request->input('sticky') : 0;
        $torrent->moderated_at = Carbon::now();
        $torrent->moderated_by = User::where('username', 'System')->first()->id; //System ID

        // Set freeleech and doubleup if featured
        if ($torrent->featured == 1) {
            $torrent->free = '1';
            $torrent->doubleup = '1';
        }

        // Validation
        $v = \validator($torrent->toArray(), [
            'name'              => 'required|unique:torrents',
            'slug'              => 'required',
            'description'       => 'required',
            'info_hash'         => 'required|unique:torrents',
            'file_name'         => 'required',
            'num_file'          => 'required|numeric',
            'announce'          => 'required',
            'size'              => 'required',
            'category_id'       => 'required|exists:categories,id',
            'type_id'           => 'required|exists:types,id',
            'resolution_id'     => 'nullable|exists:resolutions,id',
            'user_id'           => 'required|exists:users,id',
            'imdb'              => 'required|numeric',
            'tvdb'              => 'required|numeric',
            'tmdb'              => 'required|numeric',
            'mal'               => 'required|numeric',
            'igdb'              => 'required|numeric',
            'anon'              => 'required',
            'stream'            => 'required',
            'sd'                => 'required',
            'personal_release'  => 'nullable',
            'internal'          => 'required',
            'featured'          => 'required',
            'free'              => 'required',
            'doubleup'          => 'required',
            'sticky'            => 'required',
        ]);

        if ($v->fails()) {
            if (Storage::disk('torrents')->exists($fileName)) {
                Storage::disk('torrents')->delete($fileName);
            }

            return $this->sendError('Validation Error.', $v->errors());
        }
        // Save The Torrent
        $torrent->save();
        // Set torrent to featured
        if ($torrent->featured == 1) {
            $featuredTorrent = new FeaturedTorrent();
            $featuredTorrent->user_id = $user->id;
            $featuredTorrent->torrent_id = $torrent->id;
            $featuredTorrent->save();
        }
        // Count and save the torrent number in this category
        $category->num_torrent = $category->torrents_count;
        $category->save();
        // Backup the files contained in the torrent
        foreach (TorrentTools::getTorrentFiles($decodedTorrent) as $file) {
            $torrentFile = new TorrentFile();
            $torrentFile->name = $file['name'];
            $torrentFile->size = $file['size'];
            $torrentFile->torrent_id = $torrent->id;
            $torrentFile->save();
            unset($torrentFile);
        }

        $tmdbScraper = new TMDBScraper();
        if ($torrent->category->tv_meta && ($torrent->tmdb || $torrent->tmdb != 0)) {
            $tmdbScraper->tv($torrent->tmdb);
        }
        if ($torrent->category->movie_meta && ($torrent->tmdb || $torrent->tmdb != 0)) {
            $tmdbScraper->movie($torrent->tmdb);
        }

        // Torrent Keywords System
        foreach (self::parseKeywords($request->input('keywords')) as $keyword) {
            $tag = new Keyword();
            $tag->name = $keyword;
            $tag->torrent_id = $torrent->id;
            $tag->save();
        }

        // check for trusted user and update torrent
        if ($user->group->is_trusted) {
            $appurl = \config('app.url');
            $user = $torrent->user;
            $username = $user->username;
            $anon = $torrent->anon;
            $featured = $torrent->featured;
            $free = $torrent->free;
            $doubleup = $torrent->doubleup;

            // Announce To Shoutbox
            if ($anon == 0) {
                $this->chatRepository->systemMessage(
                    \sprintf('User [url=%s/users/', $appurl).$username.']'.$username.\sprintf('[/url] has uploaded a new '.$torrent->category->name.'. [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url], grab it now! :slight_smile:'
                );
            } else {
                $this->chatRepository->systemMessage(
                    \sprintf('An anonymous user has uploaded a new '.$torrent->category->name.'. [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url], grab it now! :slight_smile:'
                );
            }

            if ($anon == 1 && $featured == 1) {
                $this->chatRepository->systemMessage(
                    \sprintf('Ladies and Gents, [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url] has been added to the Featured Torrents Slider by an anonymous user! Grab It While You Can! :fire:'
                );
            } elseif ($anon == 0 && $featured == 1) {
                $this->chatRepository->systemMessage(
                    \sprintf('Ladies and Gents, [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.\sprintf('[/url] has been added to the Featured Torrents Slider by [url=%s/users/', $appurl).$username.']'.$username.'[/url]! Grab It While You Can! :fire:'
                );
            }

            if ($free == 1 && $featured == 0) {
                $this->chatRepository->systemMessage(
                    \sprintf('Ladies and Gents, [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url] has been granted 100%% FreeLeech! Grab It While You Can! :fire:'
                );
            }

            if ($doubleup == 1 && $featured == 0) {
                $this->chatRepository->systemMessage(
                    \sprintf('Ladies and Gents, [url=%s/torrents/', $appurl).$torrent->id.']'.$torrent->name.'[/url] has been granted Double Upload! Grab It While You Can! :fire:'
                );
            }

            TorrentHelper::approveHelper($torrent->id);
            \info('New API Upload', [\sprintf('User %s has uploaded %s', $user->username, $torrent->name)]);
        }

        return $this->sendResponse(\route('torrent.download.rsskey', ['id' => $torrent->id, 'rsskey' => \auth('api')->user()->rsskey]), 'Torrent uploaded successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return TorrentResource
     */
    public function show(int $id): TorrentResource
    {
        $torrent = Torrent::findOrFail($id);

        TorrentResource::withoutWrapping();

        return new TorrentResource($torrent);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): void
    {
        //
    }

    /**
     * Uses Input's To Put Together A Search.
     */
    public function filter(Request $request): \Illuminate\Http\JsonResponse | TorrentsResource
    {
        $torrent = Torrent::with(['user:id,username,group_id', 'category', 'type', 'resolution'])
            ->withCount(['thanks', 'comments'])
            ->when($request->has('name'), function ($query) use ($request) {
                $terms = \explode(' ', $request->input('name'));
                $search = '';
                foreach ($terms as $term) {
                    $search .= '%'.$term.'%';
                }
                $query->where('name', 'LIKE', $search);
            })
            ->when($request->has('description'), function ($query) use ($request) {
                $query->where('description', 'LIKE', '%'.$request->input('description').'%');
            })
            ->when($request->has('mediainfo'), function ($query) use ($request) {
                $query->where('mediainfo', 'LIKE', '%'.$request->input('mediainfo').'%');
            })
            ->when($request->has('file_name'), function ($query) use ($request) {
                $query->whereHas('files', function ($q) use ($request) {
                    $q->where('name', $request->input('file_name'));
                });
            })
            ->when($request->has('uploader'), function ($query) use ($request) {
                $match = User::where('username', 'LIKE', '%'.$request->input('uploader').'%')->orderBy('username', 'ASC')->first();
                if ($match) {
                    $query->where('user_id', '=', $match->id)->where('anon', '=', 0);
                }
            })
            ->when($request->has('keywords'), function ($query) use ($request) {
                $keywords = self::parseKeywords($request->input('keywords'));
                $keyword = Keyword::select(['torrent_id'])->whereIn('name', $keywords)->get();
                $query->whereIn('id', $keyword->torrent_id);
            })
            ->when($request->has('startYear') && $request->has('endYear'), function ($query) use ($request) {
                $query->whereBetween('release_year', [$request->input('startYear'), $request->input('endYear')]);
            })
            ->when($request->has('categories'), function ($query) use ($request) {
                $query->whereIn('category_id', $request->input('categories'));
            })
            ->when($request->has('types'), function ($query) use ($request) {
                $query->whereIn('type_id', $request->input('types'));
            })
            ->when($request->has('resolutions'), function ($query) use ($request) {
                $query->whereIn('resolution_id', $request->input('resolutions'));
            })
            ->when($request->has('genres'), function ($query) use ($request) {
                $tvCollection = DB::table('genre_tv')->whereIn('genre_id', $request->input('genres'))->pluck('tv_id');
                $movieCollection = DB::table('genre_movie')->whereIn('genre_id', $request->input('genres'))->pluck('movie_id');
                $mergedCollection = $tvCollection->merge($movieCollection);

                $query->whereIn('tmdb', $mergedCollection);
            })
            ->when($request->has('tmdbId'), function ($query) use ($request) {
                $query->where('tmdb', '=', $request->input('tmdbId'));
            })
            ->when($request->has('imdbId'), function ($query) use ($request) {
                $query->where('imdb', '=', $request->input('imdbId'));
            })
            ->when($request->has('tvdbId'), function ($query) use ($request) {
                $query->where('tvdb', '=', $request->input('tvdbId'));
            })
            ->when($request->has('malId'), function ($query) use ($request) {
                $query->where('mal', '=', $request->input('malId'));
            })
            ->when($request->has('playlistId'), function ($query) use ($request) {
                $playlist = PlaylistTorrent::where('playlist_id', '=', $request->input('playlistId'))->pluck('torrent_id');
                $query->whereIn('id', $playlist);
            })
            ->when($request->has('collectionId'), function ($query) use ($request) {
                $categories = Category::where('movie_meta', '=', 1)->pluck('id');
                $collection = DB::table('collection_movie')->where('collection_id', '=', $request->input('collectionId'))->pluck('movie_id');
                $query->whereIn('category_id', $categories)->whereIn('tmdb', $collection);
            })
            ->when($request->has('free'), function ($query) {
                $query->where('free', '=', 1);
            })
            ->when($request->has('doubleup'), function ($query) {
                $query->where('doubleup', '=', 1);
            })
            ->when($request->has('featured'), function ($query) {
                $query->where('featured', '=', 1);
            })
            ->when($request->has('stream'), function ($query) {
                $query->where('stream', '=', 1);
            })
            ->when($request->has('sd'), function ($query) {
                $query->where('sd', '=', 1);
            })
            ->when($request->has('highspeed'), function ($query) {
                $query->where('highspeed', '=', 1);
            })
            ->when($request->has('internal'), function ($query) {
                $query->where('internal', '=', 1);
            })
            ->when($request->has('personalRelease'), function ($query) {
                $query->where('personal_release', '=', 1);
            })
            ->when($request->has('alive'), function ($query) {
                $query->orWhere('seeders', '>=', 1);
            })
            ->when($request->has('dying'), function ($query) {
                $query->orWhere('seeders', '=', 1)->where('times_completed', '>=', 3);
            })
            ->when($request->has('dead'), function ($query) {
                $query->orWhere('seeders', '=', 0);
            })
            ->orderBy('sticky', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->paginate(25);

        if ($torrent !== null) {
            return new TorrentsResource($torrent);
        }

        return $this->sendResponse('404', 'No Torrents Found');
    }

    /**
     * Anonymize A Torrent Media Info.
     *
     * @param $mediainfo
     *
     * @return void
     */
    private static function anonymizeMediainfo($mediainfo): void
    {
        if ($mediainfo === null) {
            return;
        }
        $completeNameI = \strpos($mediainfo, 'Complete name');
        if ($completeNameI !== false) {
            $pathI = \strpos($mediainfo, ': ', $completeNameI);
            if ($pathI !== false) {
                $pathI += 2;
                $endI = \strpos($mediainfo, "\n", $pathI);
                $path = \substr($mediainfo, $pathI, $endI - $pathI);
                $newPath = MediaInfo::stripPath($path);

                return \substr_replace($mediainfo, $newPath, $pathI, \strlen($path));
            }
        }

        return $mediainfo;
    }

    /**
     * Parse Torrent Keywords.
     *
     * @param $text
     *
     * @return array
     */
    private static function parseKeywords($text): array
    {
        $parts = \explode(', ', $text);
        $result = [];
        foreach ($parts as $part) {
            $part = \trim($part);
            if ($part != '') {
                $result[] = $part;
            }
        }

        return $result;
    }
}
