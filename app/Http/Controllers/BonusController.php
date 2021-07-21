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

use App\Models\BonExchange;
use App\Models\BonTransactions;
use App\Models\PersonalFreeleech;
use App\Models\Post;
use App\Models\PrivateMessage;
use App\Models\Torrent;
use App\Models\User;
use App\Notifications\NewBon;
use App\Notifications\NewPostTip;
use App\Notifications\NewUploadTip;
use App\Repositories\ChatRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Feature\Http\Controllers\BonusControllerTest
 */
class BonusController extends Controller
{
    /**
     * BonusController Constructor.
     */
    public function __construct(protected \App\Interfaces\ByteUnitsInterface $byteUnits, private ChatRepository $chatRepository)
    {
    }

    /**
     * Show Bonus Gifts System.
     */
    public function gifts(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $userbon = $user->getSeedbonus();
        $gifttransactions = BonTransactions::with(['senderObj', 'receiverObj'])->where(function ($query) use ($user) {
            $query->where('sender', '=', $user->id)->orwhere('receiver', '=', $user->id);
        })->where('name', '=', 'gift')->orderBy('date_actioned', 'DESC')->paginate(25);

        $giftsSent = BonTransactions::where('sender', '=', $user->id)->where('name', '=', 'gift')->sum('cost');
        $giftsReceived = BonTransactions::where('receiver', '=', $user->id)->where('name', '=', 'gift')->sum('cost');

        return \view('bonus.gifts', [
            'user'              => $user,
            'gifttransactions'  => $gifttransactions,
            'userbon'           => $userbon,
            'gifts_sent'        => $giftsSent,
            'gifts_received'    => $giftsReceived,
        ]);
    }

    /**
     * Show Bonus Tips System.
     */
    public function tips(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $userbon = $user->getSeedbonus();
        $bontransactions = BonTransactions::with(['senderObj', 'receiverObj'])->where(function ($query) use ($user) {
            $query->where('sender', '=', $user->id)->orwhere('receiver', '=', $user->id);
        })->where('name', '=', 'tip')->orderBy('date_actioned', 'DESC')->paginate(25);

        $tipsSent = BonTransactions::where('sender', '=', $user->id)->where('name', '=', 'tip')->sum('cost');
        $tipsReceived = BonTransactions::where('receiver', '=', $user->id)->where('name', '=', 'tip')->sum('cost');

        return \view('bonus.tips', [
            'user'              => $user,
            'bontransactions'   => $bontransactions,
            'userbon'           => $userbon,
            'tips_sent'         => $tipsSent,
            'tips_received'     => $tipsReceived,
        ]);
    }

    /**
     * Show Bonus Store System.
     */
    public function store(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $userbon = $user->getSeedbonus();
        $activefl = PersonalFreeleech::where('user_id', '=', $user->id)->first();
        $BonExchange = new BonExchange();
        $bontransactions = BonTransactions::with('exchange')->where('sender', '=', $user->id)->where('itemID', '>', 0)->orderBy('date_actioned', 'DESC')->limit(25)->get();
        $uploadOptions = $BonExchange->getUploadOptions();
        $downloadOptions = $BonExchange->getDownloadOptions();
        $personalFreeleech = $BonExchange->getPersonalFreeleechOption();
        $invite = $BonExchange->getInviteOption();

        return \view('bonus.store', [
            'userbon'           => $userbon,
            'activefl'          => $activefl,
            'bontransactions'   => $bontransactions,
            'uploadOptions'     => $uploadOptions,
            'downloadOptions'   => $downloadOptions,
            'personalFreeleech' => $personalFreeleech,
            'invite'            => $invite,
        ]);
    }

    /**
     * Show Bonus Gift System.
     */
    public function gift(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $userbon = $request->user()->getSeedbonus();

        return \view('bonus.gift', [
            'userbon'           => $userbon,
        ]);
    }

    /**
     * Show Bonus Earnings System.
     */
    public function bonus(Request $request, string $username = ''): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $userbon = $request->user()->getSeedbonus();

        //Dying Torrent
        $dying = $this->getDyingCount($request);
        //Legendary Torrents
        $legendary = $this->getLegendaryCount($request);
        //Old Torrents
        $old = $this->getOldCount($request);
        //Large Torrents
        $huge = $this->getHugeCount($request);
        //Large Torrents
        $large = $this->getLargeCount($request);
        //Everyday Torrents
        $regular = $this->getRegularCount($request);

        //Participant Seeder
        $participant = $this->getParticipaintSeedCount($request);
        //TeamPlayer Seeder
        $teamplayer = $this->getTeamPlayerSeedCount($request);
        //Committed Seeder
        $committed = $this->getCommitedSeedCount($request);
        //MVP Seeder
        $mvp = $this->getMVPSeedCount($request);
        //Legend Seeder
        $legend = $this->getLegendarySeedCount($request);

        //Total points per hour
        $total =
            ($dying * 2) + ($legendary * 1.5) + ($old * 1) + ($huge * 0.75) + ($large * 0.50) + ($regular * 0.25)
            + ($participant * 0.25) + ($teamplayer * 0.50) + ($committed * 0.75) + ($mvp * 1) + ($legend * 2);

        $daily = $total * 24;
        $weekly = $total * 24 * 7;
        $monthly = $total * 24 * 30;
        $yearly = $total * 24 * 365;
        $minute = $total / 60;
        $second = $minute / 60;

        return \view('bonus.index', [
            'userbon'           => $userbon,
            'dying'             => $dying,
            'legendary'         => $legendary,
            'old'               => $old,
            'huge'              => $huge,
            'large'             => $large,
            'regular'           => $regular,
            'participant'       => $participant,
            'teamplayer'        => $teamplayer,
            'committed'         => $committed,
            'mvp'               => $mvp,
            'legend'            => $legend,
            'total'             => $total,
            'daily'             => $daily,
            'weekly'            => $weekly,
            'monthly'           => $monthly,
            'yearly'            => $yearly,
            'username'          => $username,
            'minute'            => $minute,
            'second'            => $second,
        ]);
    }

    /**
     * Exchange Points For A Item.
     */
    public function exchange(Request $request, BonExchange $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $userbon = $user->seedbonus;

        $itemCost = \resolve(BonExchange::class)->getItemCost($id);

        if ($userbon >= $itemCost) {
            $flag = $this->doItemExchange($user->id, $id);

            if ($flag === false) {
                return \redirect()->route('bonus_store')
                    ->withErrors('Bonus Exchange Failed!');
            }

            $user->seedbonus -= $itemCost;
            $user->save();
        } else {
            return \redirect()->route('bonus_store')
                ->withErrors('Bonus Exchange Failed!');
        }

        return \redirect()->route('bonus_store')
            ->withSuccess('Bonus Exchange Successful');
    }

    /**
     * Do Item Exchange.
     *
     * @param \App\Models\User        $userID
     * @param \App\Models\BonExchange $itemID
     *
     * @return bool
     */
    public function doItemExchange(User $userID, BonExchange $itemID): bool
    {
        $current = Carbon::now();
        $item = BonExchange::where('id', '=', $itemID)->get()->toArray()[0];

        $userAcc = User::findOrFail($userID);
        $activefl = PersonalFreeleech::where('user_id', '=', $userAcc->id)->first();
        $bonTransactions = \resolve(BonTransactions::class);

        if ($item['upload'] == true) {
            $userAcc->uploaded += $item['value'];
            $userAcc->save();
        } elseif ($item['download'] == true) {
            if ($userAcc->downloaded >= $item['value']) {
                $userAcc->downloaded -= $item['value'];
                $userAcc->save();
            } else {
                return false;
            }
        } elseif ($item['personal_freeleech'] == true) {
            if (! $activefl) {
                $personalFreeleech = new PersonalFreeleech();
                $personalFreeleech->user_id = $userAcc->id;
                $personalFreeleech->save();

                // Send Private Message
                $privateMessage = new PrivateMessage();
                $privateMessage->sender_id = 1;
                $privateMessage->receiver_id = $userAcc->id;
                $privateMessage->subject = 'Personal 24 Hour Freeleech Activated';
                $privateMessage->message = \sprintf('Your [b]Personal 24 Hour Freeleech[/b] session has started! It will expire on %s [b]', $current->addDays(1)->toDayDateTimeString()).\config('app.timezone').'[/b]! 
                [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
                $privateMessage->save();
            } else {
                return false;
            }
        } elseif ($item['invite'] == true) {
            $userAcc->invites += $item['value'];
            if ($userAcc->invites) {
                $userAcc->save();
            } else {
                return false;
            }
        }

        $bonTransactions->itemID = $item['id'];
        $bonTransactions->name = $item['description'];
        $bonTransactions->cost = $item['value'];
        $bonTransactions->sender = $userID;
        $bonTransactions->comment = $item['description'];
        $bonTransactions->torrent_id = null;
        $bonTransactions->save();

        return true;
    }

    /**
     * Gift Points To A User.
     */
    public function sendGift(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $v = \validator($request->all(), [
            'to_username'   => 'required|exists:users,username|max:180',
            'bonus_points'  => \sprintf('required|numeric|min:1|max:%s', $user->seedbonus),
            'bonus_message' => 'required|string',
        ]);

        $dest = 'default';
        if ($request->has('dest') && $request->input('dest') === 'profile') {
            $dest = 'profile';
        }

        if ($v->passes()) {
            $recipient = User::where('username', '=', $request->input('to_username'))->first();

            if (! $recipient || $recipient->id == $user->id) {
                return \redirect()->route('bonus_store')
                    ->withErrors('Unable to find specified user');
            }

            $value = $request->input('bonus_points');
            $recipient->seedbonus += $value;
            $recipient->save();

            $user->seedbonus -= $value;
            $user->save();

            $bonTransactions = new BonTransactions();
            $bonTransactions->itemID = 0;
            $bonTransactions->name = 'gift';
            $bonTransactions->cost = $value;
            $bonTransactions->sender = $user->id;
            $bonTransactions->receiver = $recipient->id;
            $bonTransactions->comment = $request->input('bonus_message');
            $bonTransactions->torrent_id = null;
            $bonTransactions->save();

            if ($user->id != $recipient->id && $recipient->acceptsNotification($request->user(), $recipient, 'bon', 'show_bon_gift')) {
                $recipient->notify(new NewBon('gift', $user->username, $bonTransactions));
            }

            $profileUrl = \href_profile($user);
            $recipientUrl = \href_profile($recipient);

            $this->chatRepository->systemMessage(
                \sprintf('[url=%s]%s[/url] has gifted %s BON to [url=%s]%s[/url]', $profileUrl, $user->username, $value, $recipientUrl, $recipient->username)
            );

            if ($dest === 'profile') {
                return \redirect()->route('users.show', ['username' => $recipient->username])
                    ->withSuccess('Gift Sent');
            }

            return \redirect()->route('bonus_gift')
                ->withSuccess('Gift Sent');
        }
        $v = \validator($request->all(), [
            'to_username' => 'required|exists:users,username|max:180',
        ]);
        if ($v->passes()) {
            $recipient = User::where('username', 'LIKE', $request->input('to_username'))->first();

            if (! $recipient || $recipient->id == $user->id) {
                return \redirect()->route('bonus_store')
                    ->withErrors('Unable to find specified user');
            }

            if ($dest === 'profile') {
                return \redirect()->route('users.show', ['username' => $recipient->username])
                    ->withErrors('You Must Enter An Amount And Message!');
            }

            return \redirect()->route('bonus_gift')
                ->withErrors('You Must Enter An Amount And Message!');
        }

        return \redirect()->route('bonus_store')
            ->withErrors('Unable to find specified user');
    }

    /**
     * Tip Points To A Uploader.
     */
    public function tipUploader(Request $request, Torrent $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrent = Torrent::withAnyStatus()->findOrFail($id);
        $uploader = User::where('id', '=', $torrent->user_id)->first();

        $tipAmount = $request->input('tip');
        if ($tipAmount > $user->seedbonus) {
            return \redirect()->route('torrent', ['id' => $torrent->id])
                ->withErrors('Your To Broke To Tip The Uploader!');
        }
        if ($user->id == $torrent->user_id) {
            return \redirect()->route('torrent', ['id' => $torrent->id])
                ->withErrors('You Cannot Tip Yourself!');
        }
        if ($tipAmount <= 0) {
            return \redirect()->route('torrent', ['id' => $torrent->id])
                ->withErrors('You Cannot Tip A Negative Amount!');
        }
        $uploader->seedbonus += $tipAmount;
        $uploader->save();

        $user->seedbonus -= $tipAmount;
        $user->save();

        $bonTransactions = new BonTransactions();
        $bonTransactions->itemID = 0;
        $bonTransactions->name = 'tip';
        $bonTransactions->cost = $tipAmount;
        $bonTransactions->sender = $user->id;
        $bonTransactions->receiver = $uploader->id;
        $bonTransactions->comment = 'tip';
        $bonTransactions->torrent_id = $torrent->id;
        $bonTransactions->save();

        if ($uploader->acceptsNotification($request->user(), $uploader, 'torrent', 'show_torrent_tip')) {
            $uploader->notify(new NewUploadTip('torrent', $user->username, $tipAmount, $torrent));
        }

        return \redirect()->route('torrent', ['id' => $torrent->id])
            ->withSuccess('Your Tip Was Successfully Applied!');
    }

    /**
     * Tip Points To A Poster.
     */
    public function tipPoster(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($request->has('post') && $request->input('post') > 0) {
            $post = Post::with('topic')->findOrFail($request->input('post'));
            $poster = User::where('id', '=', $post->user_id)->firstOrFail();
        } else {
            \abort(404);
        }

        $tipAmount = $request->input('tip');
        if ($tipAmount > $user->seedbonus) {
            return \redirect()->route('forum_topic', ['id' => $post->topic->id])
                ->withErrors('You Are To Broke To Tip The Poster!');
        }
        if ($user->id == $poster->id) {
            return \redirect()->route('forum_topic', ['id' => $post->topic->id])
                ->withErrors('You Cannot Tip Yourself!');
        }
        if ($tipAmount <= 0) {
            return \redirect()->route('forum_topic', ['id' => $post->topic->id])
                ->withErrors('You Cannot Tip A Negative Amount!');
        }
        $poster->seedbonus += $tipAmount;
        $poster->save();

        $user->seedbonus -= $tipAmount;
        $user->save();

        $bonTransactions = new BonTransactions();
        $bonTransactions->itemID = 0;
        $bonTransactions->name = 'tip';
        $bonTransactions->cost = $tipAmount;
        $bonTransactions->sender = $user->id;
        $bonTransactions->receiver = $poster->id;
        $bonTransactions->comment = 'tip';
        $bonTransactions->post_id = $post->id;
        $bonTransactions->save();

        $poster->notify(new NewPostTip('forum', $user->username, $tipAmount, $post));

        return \redirect()->route('forum_topic', ['id' => $post->topic->id])
            ->withSuccess('Your Tip Was Successfully Applied!');
    }

    /**
     * @method getDyingCount
     */
    public function getDyingCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.seeders', 1)
            ->where('torrents.times_completed', '>', 2)
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getLegendaryCount
     */
    public function getLegendaryCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->whereRaw('torrents.created_at < date_sub(now(), interval 12 month)')
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->where('peers.seeder', 1)
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getOldCount
     */
    public function getOldCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->whereRaw('torrents.created_at < date_sub(now(), Interval 6 month)')
            ->whereRaw('torrents.created_at > date_sub(now(), interval 12 month)')
            ->whereRaw('date_sub(peers.created_at,interval 30 minute) < now()')
            ->where('peers.seeder', 1)
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getHugeCount
     */
    public function getHugeCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $this->byteUnits->bytesFromUnit('100GiB'))
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getLargeCount
     */
    public function getLargeCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $this->byteUnits->bytesFromUnit('25GiB'))
            ->where('torrents.size', '<', $this->byteUnits->bytesFromUnit('100GiB'))
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getRegularCount
     */
    public function getRegularCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('peers')
            ->select('peers.hash')->distinct()
            ->join('torrents', 'torrents.id', '=', 'peers.torrent_id')
            ->where('peers.seeder', 1)
            ->where('torrents.size', '>=', $this->byteUnits->bytesFromUnit('1GiB'))
            ->where('torrents.size', '<', $this->byteUnits->bytesFromUnit('25GiB'))
            ->where('peers.user_id', $user->id)
            ->count();
    }

    /**
     * @method getParticipaintSeedCount
     */
    public function getParticipaintSeedCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('history')
            ->select('history.seedtime')->distinct()
            ->join('torrents', 'torrents.info_hash', '=', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000)
            ->where('history.seedtime', '<', 2_592_000 * 2)
            ->where('history.user_id', $user->id)
            ->count();
    }

    /**
     * @method getParticipaintSeedCount
     */
    public function getTeamPlayerSeedCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('history')
            ->select('history.seedtime')->distinct()
            ->join('torrents', 'torrents.info_hash', '=', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 2)
            ->where('history.seedtime', '<', 2_592_000 * 3)
            ->where('history.user_id', $user->id)
            ->count();
    }

    /**
     * @method getParticipaintSeedCount
     */
    public function getCommitedSeedCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('history')
            ->select('history.seedtime')->distinct()
            ->join('torrents', 'torrents.info_hash', '=', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 3)
            ->where('history.seedtime', '<', 2_592_000 * 6)
            ->where('history.user_id', $user->id)
            ->count();
    }

    /**
     * @method getParticipaintSeedCount
     */
    public function getMVPSeedCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('history')
            ->select('history.seedtime')->distinct()
            ->join('torrents', 'torrents.info_hash', '=', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 6)
            ->where('history.seedtime', '<', 2_592_000 * 12)
            ->where('history.user_id', $user->id)
            ->count();
    }

    /**
     * @method getParticipaintSeedCount
     */
    public function getLegendarySeedCount(Request $request): int
    {
        $user = $request->user();

        return DB::table('history')
            ->select('history.seedtime')->distinct()
            ->join('torrents', 'torrents.info_hash', '=', 'history.info_hash')
            ->where('history.active', 1)
            ->where('history.seedtime', '>=', 2_592_000 * 12)
            ->where('history.user_id', $user->id)
            ->count();
    }
}
