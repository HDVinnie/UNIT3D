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

use App\Achievements\UserFilled100Requests;
use App\Achievements\UserFilled25Requests;
use App\Achievements\UserFilled50Requests;
use App\Achievements\UserFilled75Requests;
use App\Models\BonTransactions;
use App\Models\Category;
use App\Models\Movie;
use App\Models\Resolution;
use App\Models\Torrent;
use App\Models\TorrentRequest;
use App\Models\TorrentRequestBounty;
use App\Models\TorrentRequestClaim;
use App\Models\Tv;
use App\Models\Type;
use App\Models\User;
use App\Notifications\NewRequestBounty;
use App\Notifications\NewRequestClaim;
use App\Notifications\NewRequestFill;
use App\Notifications\NewRequestFillApprove;
use App\Notifications\NewRequestFillReject;
use App\Notifications\NewRequestUnclaim;
use App\Repositories\ChatRepository;
use App\Services\Tmdb\TMDBScraper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MarcReichel\IGDBLaravel\Models\Game;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\RequestControllerTest
 */
class RequestController extends Controller
{
    /**
     * RequestController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('torrent_request.index');
    }

    /**
     * Display The Torrent Request.
     *
     * @param \Illuminate\Http\Request   $request
     * @param \App\Models\TorrentRequest $id
     *
     * @throws \JsonException
     * @throws \MarcReichel\IGDBLaravel\Exceptions\MissingEndpointException
     * @throws \ReflectionException
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function request(Request $request, TorrentRequest $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $torrentRequest = TorrentRequest::findOrFail($id);
        $user = $request->user();
        $torrentRequestClaim = TorrentRequestClaim::where('request_id', '=', $id)->first();
        $voters = $torrentRequest->requestBounty()->get();
        $comments = $torrentRequest->comments()->latest('created_at')->paginate(6);
        $carbon = Carbon::now()->addDay();

        $meta = null;
        if ($torrentRequest->category->tv_meta && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $meta = Tv::with('genres', 'networks', 'seasons')->where('id', '=', $torrentRequest->tmdb)->first();
        }
        if ($torrentRequest->category->movie_meta && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $meta = Movie::with('genres', 'cast', 'companies', 'collection')->where('id', '=', $torrentRequest->tmdb)->first();
        }
        if ($torrentRequest->category->game_meta && ($torrentRequest->igdb || $torrentRequest->igdb != 0)) {
            $meta = Game::with([
                'cover'    => ['url', 'image_id'],
                'artworks' => ['url', 'image_id'],
                'genres'   => ['name'],
            ])->find($torrentRequest->igdb);
        }

        return \view('requests.request', [
            'torrentRequest'      => $torrentRequest,
            'voters'              => $voters, 'user' => $user,
            'comments'            => $comments,
            'carbon'              => $carbon,
            'meta'                => $meta,
            'torrentRequestClaim' => $torrentRequestClaim,
        ]);
    }

    /**
     * Torrent Request Add Form.
     */
    public function addRequestForm(Request $request, string $title = '', int $imdb = 0, int $tmdb = 0): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();

        return \view('requests.add_request', [
            'categories'  => Category::all()->sortBy('position'),
            'types'       => Type::all()->sortBy('position'),
            'resolutions' => Resolution::all()->sortBy('position'),
            'user'        => $user,
            'title'       => $title,
            'imdb'        => \str_replace('tt', '', $imdb),
            'tmdb'        => $tmdb,
        ]);
    }

    /**
     * Store A New Torrent Request.
     */
    public function addrequest(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $category = Category::findOrFail($request->input('category_id'));
        $torrentRequest = new TorrentRequest();
        $torrentRequest->name = $request->input('name');
        $torrentRequest->description = $request->input('description');
        $torrentRequest->category_id = $category->id;
        $torrentRequest->user_id = $user->id;
        $torrentRequest->imdb = $request->input('imdb');
        $torrentRequest->tvdb = $request->input('tvdb');
        $torrentRequest->tmdb = $request->input('tmdb');
        $torrentRequest->mal = $request->input('mal');
        $torrentRequest->igdb = $request->input('igdb');
        $torrentRequest->type_id = $request->input('type_id');
        $torrentRequest->resolution_id = $request->input('resolution_id');
        $torrentRequest->bounty = $request->input('bounty');
        $torrentRequest->votes = 1;
        $torrentRequest->anon = $request->input('anon');

        $v = \validator($torrentRequest->toArray(), [
            'name'          => 'required|max:180',
            'imdb'          => 'required|numeric',
            'tvdb'          => 'required|numeric',
            'tmdb'          => 'required|numeric',
            'mal'           => 'required|numeric',
            'igdb'          => 'required|numeric',
            'category_id'   => 'required|exists:categories,id',
            'type_id'       => 'required|exists:types,id',
            'resolution_id' => 'nullable|exists:resolutions,id',
            'description'   => 'required|string',
            'bounty'        => \sprintf('required|numeric|min:0|max:%s', $user->seedbonus),
            'anon'          => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('requests.index')
                ->withErrors($v->errors())->withInput();
        }
        $torrentRequest->save();

        $tmdbScraper = new TMDBScraper();
        if ($torrentRequest->category->tv_meta !== 0 && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $tmdbScraper->tv($torrentRequest->tmdb);
        }

        if ($torrentRequest->category->movie_meta !== 0 && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $tmdbScraper->movie($torrentRequest->tmdb);
        }

        $torrentRequestBounty = new TorrentRequestBounty();
        $torrentRequestBounty->user_id = $user->id;
        $torrentRequestBounty->seedbonus = $request->input('bounty');
        $torrentRequestBounty->requests_id = $torrentRequest->id;
        $torrentRequestBounty->anon = $request->input('anon');
        $torrentRequestBounty->save();

        $BonTransactions = new BonTransactions();
        $BonTransactions->itemID = 0;
        $BonTransactions->name = 'request';
        $BonTransactions->cost = $request->input('bounty');
        $BonTransactions->sender = $user->id;
        $BonTransactions->receiver = 0;
        $BonTransactions->comment = \sprintf('new request - %s', $request->input('name'));
        $BonTransactions->save();
        $user->seedbonus -= $request->input('bounty');
        $user->save();
        $trUrl = \href_request($torrentRequest);
        $profileUrl = \href_profile($user);
        // Auto Shout
        if ($torrentRequest->anon == 0) {
            $this->chatRepository->systemMessage(
                \sprintf('[url=%s]%s[/url] has created a new request [url=%s]%s[/url]', $profileUrl, $user->username, $trUrl, $torrentRequest->name)
            );
        } else {
            $this->chatRepository->systemMessage(
                \sprintf('An anonymous user has created a new request [url=%s]%s[/url]', $trUrl, $torrentRequest->name)
            );
        }

        return \redirect()->route('requests.index')
            ->withSuccess('Request Added.');
    }

    /**
     * Torrent Request Edit Form.
     */
    public function editRequestForm(Request $request, TorrentRequest $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::findOrFail($id);

        return \view('requests.edit_request', [
            'categories'     => Category::all()->sortBy('position'),
            'types'          => Type::all()->sortBy('position'),
            'resolutions'    => Resolution::all()->sortBy('position'),
            'user'           => $user,
            'torrentRequest' => $torrentRequest, ]);
    }

    /**
     * Edit A Torrent Request.
     */
    public function editrequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::findOrFail($id);
        \abort_unless($user->group->is_modo || $user->id === $torrentRequest->user_id, 403);

        // Find the right category
        $name = $request->input('name');
        $imdb = $request->input('imdb');
        $tvdb = $request->input('tvdb');
        $tmdb = $request->input('tmdb');
        $mal = $request->input('mal');
        $igdb = $request->input('igdb');
        $category = $request->input('category_id');
        $type = $request->input('type_id');
        $resolution = $request->input('resolution_id');
        $description = $request->input('description');
        $anon = $request->input('anon');

        $torrentRequest->name = $name;
        $torrentRequest->imdb = $imdb;
        $torrentRequest->tvdb = $tvdb;
        $torrentRequest->tmdb = $tmdb;
        $torrentRequest->mal = $mal;
        $torrentRequest->igdb = $igdb;
        $torrentRequest->category_id = $category;
        $torrentRequest->type_id = $type;
        $torrentRequest->resolution_id = $resolution;
        $torrentRequest->description = $description;
        $torrentRequest->anon = $anon;

        $v = \validator($torrentRequest->toArray(), [
            'name'          => 'required|max:180',
            'imdb'          => 'required|numeric',
            'tvdb'          => 'required|numeric',
            'tmdb'          => 'required|numeric',
            'mal'           => 'required|numeric',
            'igdb'          => 'required|numeric',
            'category_id'   => 'required|exists:categories,id',
            'type_id'       => 'required|exists:types,id',
            'resolution_id' => 'nullable|exists:resolutions,id',
            'description'   => 'required|string',
            'anon'          => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('requests.index')
                ->withErrors($v->errors());
        }
        $torrentRequest->save();

        $tmdbScraper = new TMDBScraper();
        if ($torrentRequest->category->tv_meta && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $tmdbScraper->tv($torrentRequest->tmdb);
        }

        if ($torrentRequest->category->movie_meta && ($torrentRequest->tmdb || $torrentRequest->tmdb != 0)) {
            $tmdbScraper->movie($torrentRequest->tmdb);
        }

        return \redirect()->route('request', ['id' => $torrentRequest->id])
            ->withSuccess('Request Edited Successfully.');
    }

    /**
     * Add Bounty To A Torrent Request.
     */
    public function addBonus(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $tr = TorrentRequest::with('user')->findOrFail($id);
        $tr->votes++;
        $tr->bounty += $request->input('bonus_value');
        $tr->created_at = Carbon::now();

        $v = \validator($request->all(), [
            'bonus_value' => \sprintf('required|numeric|min:100|max:%s', $user->seedbonus),
        ]);

        if ($v->fails()) {
            return \redirect()->route('request', ['id' => $tr->id])
                ->withErrors($v->errors());
        }
        $tr->save();
        $torrentRequestBounty = new TorrentRequestBounty();
        $torrentRequestBounty->user_id = $user->id;
        $torrentRequestBounty->seedbonus = $request->input('bonus_value');
        $torrentRequestBounty->requests_id = $tr->id;
        $torrentRequestBounty->anon = $request->input('anon');
        $torrentRequestBounty->save();
        $BonTransactions = new BonTransactions();
        $BonTransactions->itemID = 0;
        $BonTransactions->name = 'request';
        $BonTransactions->cost = $request->input('bonus_value');
        $BonTransactions->sender = $user->id;
        $BonTransactions->receiver = 0;
        $BonTransactions->comment = \sprintf('adding bonus to %s', $tr->name);
        $BonTransactions->save();
        $user->seedbonus -= $request->input('bonus_value');
        $user->save();
        $trUrl = \href_request($tr);
        $profileUrl = \href_profile($user);
        // Auto Shout
        if ($torrentRequestBounty->anon == 0) {
            $this->chatRepository->systemMessage(
                \sprintf('[url=%s]%s[/url] has added %s BON bounty to request [url=%s]%s[/url]', $profileUrl, $user->username, $request->input('bonus_value'), $trUrl, $tr->name)
            );
        } else {
            $this->chatRepository->systemMessage(
                \sprintf('An anonymous user added %s BON bounty to request [url=%s]%s[/url]', $request->input('bonus_value'), $trUrl, $tr->name)
            );
        }
        $sender = $request->input('anon') == 1 ? 'Anonymous' : $user->username;
        $requester = $tr->user;
        if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_bounty')) {
            $requester->notify(new NewRequestBounty('torrent', $sender, $request->input('bonus_value'), $tr));
        }

        return \redirect()->route('request', ['id' => $request->input('request_id')])
            ->withSuccess('Your bonus has been successfully added.');
    }

    /**
     * Fill A Torrent Request.
     */
    public function fillRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $torrentRequest = TorrentRequest::findOrFail($id);
        $torrentRequest->filled_by = $user->id;
        $torrentRequest->filled_hash = $request->input('info_hash');
        $torrentRequest->filled_when = Carbon::now();
        $torrentRequest->filled_anon = $request->input('filled_anon');

        $v = \validator($request->all(), [
            'request_id'  => 'required|exists:requests,id',
            'info_hash'   => 'required|exists:torrents,info_hash',
            'filled_anon' => 'required',
        ]);

        $torrent = Torrent::withAnyStatus()->where('info_hash', '=', $torrentRequest->filled_hash)->first();
        if ($torrent->isApproved() === false) {
            return \redirect()->route('request', ['id' => $request->input('request_id')])
                ->withErrors('The torrent info_hash you are trying to use is valid in our database but is still pending moderation. Please wait for your torrent to be approved and then try again.');
        }

        if ($v->fails()) {
            return \redirect()->route('request', ['id' => $request->input('request_id')])
                ->withErrors($v->errors());
        }
        $torrentRequest->save();
        // Send Private Message
        $sender = $request->input('filled_anon') == 1 ? 'Anonymous' : $user->username;
        $requester = $torrentRequest->user;
        if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_fill')) {
            $requester->notify(new NewRequestFill('torrent', $sender, $torrentRequest));
        }

        return \redirect()->route('request', ['id' => $request->input('request_id')])
            ->withSuccess('Your request fill is pending approval by the Requester.');
    }

    /**
     * Approve A Torrent Request.
     */
    public function approveRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $tr = TorrentRequest::findOrFail($id);

        if ($user->id == $tr->user_id || $request->user()->group->is_modo) {
            if ($tr->approved_by != null) {
                return \redirect()->route('request', ['id' => $id])
                    ->withErrors('Seems this request was already approved');
            }
            $tr->approved_by = $user->id;
            $tr->approved_when = Carbon::now();
            $tr->save();

            //BON and torrent request hash code below
            $fillUser = User::findOrFail($tr->filled_by);
            $fillAmount = $tr->bounty;

            $BonTransactions = new BonTransactions();
            $BonTransactions->itemID = 0;
            $BonTransactions->name = 'request';
            $BonTransactions->cost = $fillAmount;
            $BonTransactions->sender = 0;
            $BonTransactions->receiver = $fillUser->id;
            $BonTransactions->comment = \sprintf('%s has filled %s and has been awarded %s BONUS.', $fillUser->username, $tr->name, $fillAmount);
            $BonTransactions->save();

            $fillUser->seedbonus += $fillAmount;
            $fillUser->save();

            // Achievements
            $fillUser->addProgress(new UserFilled25Requests(), 1);
            $fillUser->addProgress(new UserFilled50Requests(), 1);
            $fillUser->addProgress(new UserFilled75Requests(), 1);
            $fillUser->addProgress(new UserFilled100Requests(), 1);

            $trUrl = \href_request($tr);
            $profileUrl = \href_profile($fillUser);

            // Auto Shout
            if ($tr->filled_anon == 0) {
                $this->chatRepository->systemMessage(
                    \sprintf('[url=%s]%s[/url] has filled request, [url=%s]%s[/url]', $profileUrl, $fillUser->username, $trUrl, $tr->name)
                );
            } else {
                $this->chatRepository->systemMessage(
                    \sprintf('An anonymous user has filled request, [url=%s]%s[/url]', $trUrl, $tr->name)
                );
            }

            $requester = $fillUser;
            if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_fill_approve')) {
                $requester->notify(new NewRequestFillApprove('torrent', $user->username, $tr));
            }

            if ($tr->filled_anon == 0) {
                return \redirect()->route('request', ['id' => $id])
                    ->withSuccess(\sprintf('You have approved %s and the bounty has been awarded to %s', $tr->name, $fillUser->username));
            }

            return \redirect()->route('request', ['id' => $id])
                ->withSuccess(\sprintf('You have approved %s and the bounty has been awarded to a anonymous user', $tr->name));
        }

        return \redirect()->route('request', ['id' => $id])
                ->withErrors("You don't have access to approve this request");
    }

    /**
     * Reject A Torrent Request.
     */
    public function rejectRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::findOrFail($id);

        if ($user->id == $torrentRequest->user_id) {
            if ($torrentRequest->approved_by != null) {
                return \redirect()->route('request', ['id' => $id])
                    ->withErrors('Seems this request was already rejected');
            }

            $requester = User::findOrFail($torrentRequest->filled_by);
            if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_fill_reject')) {
                $requester->notify(new NewRequestFillReject('torrent', $user->username, $torrentRequest));
            }

            $torrentRequest->filled_by = null;
            $torrentRequest->filled_when = null;
            $torrentRequest->filled_hash = null;
            $torrentRequest->save();

            return \redirect()->route('request', ['id' => $id])
                ->withSuccess('This request has been reset.');
        }

        return \redirect()->route('request', ['id' => $id])
            ->withSuccess("You don't have access to approve this request");
    }

    /**
     * Delete A Torrent Request.
     *
     *
     *@throws \Exception
     */
    public function deleteRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::findOrFail($id);

        if ($user->group->is_modo || $torrentRequest->user_id == $user->id) {
            $name = $torrentRequest->name;
            $torrentRequest->delete();

            return \redirect()->route('requests.index')
                ->withSuccess(\sprintf('You have deleted %s', $name));
        }

        return \redirect()->route('request', ['id' => $id])
            ->withErrors("You don't have access to delete this request.");
    }

    /**
     * Claim A Torrent Request.
     */
    public function claimRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::with('user')->findOrFail($id);

        if ($torrentRequest->claimed == null) {
            $torrentRequestClaim = new TorrentRequestClaim();
            $torrentRequestClaim->request_id = $id;
            $torrentRequestClaim->username = $user->username;
            $torrentRequestClaim->anon = $request->input('anon');
            $torrentRequestClaim->save();

            $torrentRequest->claimed = 1;
            $torrentRequest->save();

            $sender = $request->input('anon') == 1 ? 'Anonymous' : $user->username;

            $requester = $torrentRequest->user;
            if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_claim')) {
                $requester->notify(new NewRequestClaim('torrent', $sender, $torrentRequest));
            }

            return \redirect()->route('request', ['id' => $id])
                ->withSuccess('Request Successfully Claimed');
        }

        return \redirect()->route('request', ['id' => $id])
            ->withErrors('Someone else has already claimed this request buddy.');
    }

    /**
     * Uncliam A Torrent Request.
     *
     *
     *@throws \Exception
     */
    public function unclaimRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrentRequest = TorrentRequest::findOrFail($id);
        $claimer = TorrentRequestClaim::where('request_id', '=', $id)->first();

        \abort_unless($user->group->is_modo || $user->username == $claimer->username, 403);

        if ($torrentRequest->claimed == 1) {
            $requestClaim = TorrentRequestClaim::where('request_id', '=', $id)->firstOrFail();
            $isAnon = $requestClaim->anon;
            $requestClaim->delete();

            $torrentRequest->claimed = null;
            $torrentRequest->save();

            $sender = $isAnon == 1 ? 'Anonymous' : $user->username;

            $requester = $torrentRequest->user;
            if ($requester->acceptsNotification($request->user(), $requester, 'request', 'show_request_unclaim')) {
                $requester->notify(new NewRequestUnclaim('torrent', $sender, $torrentRequest));
            }

            return \redirect()->route('request', ['id' => $id])
                ->withSuccess('Request Successfully Un-Claimed');
        }

        return \redirect()->route('request', ['id' => $id])
            ->withErrors('Nothing To Unclaim.');
    }

    /**
     * Resets the filled and approved attributes on a given request.
     */
    public function resetRequest(Request $request, TorrentRequest $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_modo, 403);

        $torrentRequest = TorrentRequest::findOrFail($id);
        $torrentRequest->filled_by = null;
        $torrentRequest->filled_when = null;
        $torrentRequest->filled_hash = null;
        $torrentRequest->approved_by = null;
        $torrentRequest->approved_when = null;
        $torrentRequest->save();

        return \redirect()->route('request', ['id' => $id])
            ->withSuccess('The request has been reset!');
    }
}
