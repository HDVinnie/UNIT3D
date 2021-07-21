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

namespace App\Bots;

use App\Events\Chatter;
use App\Http\Resources\UserAudibleResource;
use App\Http\Resources\UserEchoResource;
use App\Models\Ban;
use App\Models\Bot;
use App\Models\BotTransaction;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\User;
use App\Models\UserAudible;
use App\Models\UserEcho;
use App\Models\Warning;
use App\Repositories\ChatRepository;
use Carbon\Carbon;

class NerdBot
{
    private $bot;

    private $chat;

    private $target;

    private $type;

    private $message;

    private $targeted;

    private $log;

    private Carbon $expiresAt;

    private Carbon $current;

    /**
     * NerdBot Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
        $bot = Bot::where('id', '=', '2')->firstOrFail();
        $this->bot = $bot;
        $this->expiresAt = Carbon::now()->addMinutes(60);
        $this->current = Carbon::now();
    }

    /**
     * Replace Vars.
     *
     * @param $output
     *
     * @return array|string
     */
    public function replaceVars($output): array | string
    {
        $output = \str_replace(['{me}', '{command}'], [$this->bot->name, $this->bot->command], $output);
        if (\str_contains($output, '{bots}')) {
            $botHelp = '';
            $bots = Bot::where('active', '=', 1)->where('id', '!=', $this->bot->id)->orderBy('position', 'asc')->get();
            foreach ($bots as $bot) {
                $botHelp .= '( ! | / | @)'.$bot->command.' help triggers help file for '.$bot->name."\n";
            }
            $output = \str_replace('{bots}', $botHelp, $output);
        }

        return $output;
    }

    /**
     * Get Banker.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getBanker(string $duration = 'default'): string
    {
        $banker = \cache()->get('nerdbot-banker');
        if (! $banker) {
            $banker = User::latest('seedbonus')->first();
            \cache()->put('nerdbot-banker', $banker, $this->expiresAt);
        }

        return \sprintf('Currently [url=/users/%s]%s[/url] Is The Top BON Holder On ', $banker->username, $banker->username).\config('other.title').'!';
    }

    /**
     * Get Snatched.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getSnatched(string $duration = 'default'): string
    {
        $snatched = \cache()->get('nerdbot-snatched');
        if (! $snatched) {
            $snatched = Torrent::latest('times_completed')->first();
            \cache()->put('nerdbot-snatched', $snatched, $this->expiresAt);
        }

        return \sprintf('Currently [url=/torrents/%s]%s[/url] Is The Most Snatched Torrent On ', $snatched->id, $snatched->name).\config('other.title').'!';
    }

    /**
     * Get Leeched.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getLeeched(string $duration = 'default'): string
    {
        $leeched = \cache()->get('nerdbot-leeched');
        if (! $leeched) {
            $leeched = Torrent::latest('leechers')->first();
            \cache()->put('nerdbot-leeched', $leeched, $this->expiresAt);
        }

        return \sprintf('Currently [url=/torrents/%s]%s[/url] Is The Most Leeched Torrent On ', $leeched->id, $leeched->name).\config('other.title').'!';
    }

    /**
     * Get Seeded.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getSeeded(string $duration = 'default'): string
    {
        $seeded = \cache()->get('nerdbot-seeded');
        if (! $seeded) {
            $seeded = Torrent::latest('seeders')->first();
            \cache()->put('nerdbot-seeded', $seeded, $this->expiresAt);
        }

        return \sprintf('Currently [url=/torrents/%s]%s[/url] Is The Most Seeded Torrent On ', $seeded->id, $seeded->name).\config('other.title').'!';
    }

    /**
     * Get FL.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getFreeleech(string $duration = 'default'): string
    {
        $fl = \cache()->get('nerdbot-fl');
        if (! $fl) {
            $fl = Torrent::where('free', '=', 1)->count();
            \cache()->put('nerdbot-fl', $fl, $this->expiresAt);
        }

        return \sprintf('There Are Currently %s Freeleech Torrents On ', $fl).\config('other.title').'!';
    }

    /**
     * Get DU.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getDoubleUpload(string $duration = 'default'): string
    {
        $du = \cache()->get('nerdbot-doubleup');
        if (! $du) {
            $du = Torrent::where('doubleup', '=', 1)->count();
            \cache()->put('nerdbot-doubleup', $du, $this->expiresAt);
        }

        return \sprintf('There Are Currently %s Double Upload Torrents On ', $du).\config('other.title').'!';
    }

    /**
     * Get Peers.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getPeers(string $duration = 'default'): string
    {
        $peers = \cache()->get('nerdbot-peers');
        if (! $peers) {
            $peers = Peer::count();
            \cache()->put('nerdbot-peers', $peers, $this->expiresAt);
        }

        return \sprintf('Currently There Are %s Peers On ', $peers).\config('other.title').'!';
    }

    /**
     * Get Bans.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getBans(string $duration = 'default'): string
    {
        $bans = \cache()->get('nerdbot-bans');
        if (! $bans) {
            $bans = Ban::whereNull('unban_reason')->whereNull('removed_at')->where('created_at', '>', $this->current->subDay())->count();
            \cache()->put('nerdbot-bans', $bans, $this->expiresAt);
        }

        return \sprintf('In The Last 24 Hours %s Users Have Been Banned From ', $bans).\config('other.title').'!';
    }

    /**
     * Get Warnings.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getWarnings(string $duration = 'default'): string
    {
        $warnings = \cache()->get('nerdbot-warnings');
        if (! $warnings) {
            $warnings = Warning::where('created_at', '>', $this->current->subDay())->count();
            \cache()->put('nerdbot-warnings', $warnings, $this->expiresAt);
        }

        return \sprintf('In The Last 24 Hours %s Hit and Run Warnings Have Been Issued On ', $warnings).\config('other.title').'!';
    }

    /**
     * Get Uploads.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getUploads(string $duration = 'default'): string
    {
        $uploads = \cache()->get('nerdbot-uploads');
        if (! $uploads) {
            $uploads = Torrent::where('created_at', '>', $this->current->subDay())->count();
            \cache()->put('nerdbot-uploads', $uploads, $this->expiresAt);
        }

        return \sprintf('In The Last 24 Hours %s Torrents Have Been Uploaded To ', $uploads).\config('other.title').'!';
    }

    /**
     * Get Logins.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getLogins(string $duration = 'default'): string
    {
        $logins = \cache()->get('nerdbot-logins');
        if (! $logins) {
            $logins = User::whereNotNull('last_login')->where('last_login', '>', $this->current->subDay())->count();
            \cache()->put('nerdbot-logins', $logins, $this->expiresAt);
        }

        return \sprintf('In The Last 24 Hours %s Unique Users Have Logged Into ', $logins).\config('other.title').'!';
    }

    /**
     * Get Registrations.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getRegistrations(string $duration = 'default'): string
    {
        $registrations = \cache()->get('nerdbot-users');
        if (! $registrations) {
            $registrations = User::where('created_at', '>', $this->current->subDay())->count();
            \cache()->put('nerdbot-users', $registrations, $this->expiresAt);
        }

        return \sprintf('In The Last 24 Hours %s Users Have Registered To ', $registrations).\config('other.title').'!';
    }

    /**
     * Get Bot Donations.
     *
     * @param string $duration
     *
     *@throws \Exception
     *
     * @return string
     */
    public function getDonations(string $duration = 'default'): string
    {
        $donations = \cache()->get('nerdbot-donations');
        if (! $donations) {
            $donations = BotTransaction::with('user', 'bot')->where('to_bot', '=', 1)->latest()->limit(10)->get();
            \cache()->put('nerdbot-donations', $donations, $this->expiresAt);
        }
        $donationDump = '';
        $i = 1;
        foreach ($donations as $donation) {
            $donationDump .= '#'.$i.'. '.$donation->user->username.' sent '.$donation->bot->name.' '.$donation->cost.' '.$donation->forHumans().".\n";
            $i++;
        }

        return "The Most Recent Donations To All Bots Are As Follows:\n\n".\trim($donationDump);
    }

    /**
     * Get Help.
     */
    public function getHelp()
    {
        return $this->replaceVars($this->bot->help);
    }

    /**
     * Get King.
     */
    public function getKing(): string
    {
        return \config('other.title').' Is King!';
    }

    /**
     * Send Bot Donation.
     *
     * @param int    $amount
     * @param string $note
     *
     *@throws \Exception
     *
     * @return string
     */
    public function putDonate(int $amount = 0, string $note = ''): string
    {
        $output = \implode((array) ' ', $note);
        $v = \validator(['bot_id' => $this->bot->id, 'amount'=> $amount, 'note'=> $output], [
            'bot_id'   => 'required|exists:bots,id|max:999',
            'amount'   => \sprintf('required|numeric|min:1|max:%s', $this->target->seedbonus),
            'note'     => 'required|string',
        ]);
        if ($v->passes()) {
            $value = $amount;
            $this->bot->seedbonus += $value;
            $this->bot->save();

            $this->target->seedbonus -= $value;
            $this->target->save();

            $botTransaction = new BotTransaction();
            $botTransaction->type = 'bon';
            $botTransaction->cost = $value;
            $botTransaction->user_id = $this->target->id;
            $botTransaction->bot_id = $this->bot->id;
            $botTransaction->to_bot = 1;
            $botTransaction->comment = $output;
            $botTransaction->save();

            $donations = BotTransaction::with('user', 'bot')->where('bot_id', '=', $this->bot->id)->where('to_bot', '=', 1)->latest()->limit(10)->get();
            \cache()->put('casinobot-donations', $donations, $this->expiresAt);

            return 'Your donation to '.$this->bot->name.' for '.$amount.' BON has been sent!';
        }

        return 'Your donation to '.$output.' could not be sent.';
    }

    /**
     * Process Message.
     *
     * @param                  $type
     * @param \App\Models\User $user
     * @param string           $message
     * @param int              $targeted
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function process($type, User $user, string $message = '', int $targeted = 0): bool
    {
        $this->target = $user;
        if ($type === 'message') {
            $x = 0;
            $y = 1;
            $z = 2;
        } else {
            $x = 1;
            $y = 2;
            $z = 3;
        }

        if ($message === '') {
            $log = '';
        } else {
            $log = 'All '.$this->bot->name.' commands must be a private message or begin with /'.$this->bot->command.' or !'.$this->bot->command.'. Need help? Type /'.$this->bot->command.' help and you shall be helped.';
        }
        $command = @\explode(' ', $message);

        $wildcard = null;
        $params = $command[$y] ?? null;

        if ($params != null) {
            $clone = $command;
            \array_shift($clone);
            \array_shift($clone);
            \array_shift($clone);
            $wildcard = $clone;
        }

        if (\array_key_exists($x, $command)) {
            if ($command[$x] === 'banker') {
                $log = $this->getBanker($params);
            }
            if ($command[$x] === 'bans') {
                $log = $this->getBans($params);
            }
            if ($command[$x] === 'donations') {
                $log = $this->getDonations($params);
            }
            if ($command[$x] === 'donate') {
                $log = $this->putDonate($params, $wildcard);
            }
            if ($command[$x] === 'doubleupload') {
                $log = $this->getDoubleUpload($params);
            }
            if ($command[$x] === 'freeleech') {
                $log = $this->getFreeleech($params);
            }
            if ($command[$x] === 'help') {
                $log = $this->getHelp();
            }
            if ($command[$x] === 'king') {
                $log = $this->getKing();
            }
            if ($command[$x] === 'logins') {
                $log = $this->getLogins($params);
            }
            if ($command[$x] === 'peers') {
                $log = $this->getPeers($params);
            }
            if ($command[$x] === 'registrations') {
                $log = $this->getRegistrations($params);
            }
            if ($command[$x] === 'uploads') {
                $log = $this->getUploads($params);
            }
            if ($command[$x] === 'warnings') {
                $log = $this->getWarnings($params);
            }
            if ($command[$x] === 'seeded') {
                $log = $this->getSeeded($params);
            }
            if ($command[$x] === 'leeched') {
                $log = $this->getLeeched($params);
            }
            if ($command[$x] === 'snatched') {
                $log = $this->getSnatched($params);
            }
        }
        $this->targeted = $targeted;
        $this->type = $type;
        $this->message = $message;
        $this->log = $log;

        return $this->pm();
    }

    /**
     * Output Message.
     */
    public function pm(): \Illuminate\Http\Response | bool | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        $type = $this->type;
        $target = $this->target;
        $txt = $this->log;
        $message = $this->message;
        $targeted = $this->targeted;

        if ($type === 'message' || $type === 'private') {
            $receiverDirty = 0;
            $receiverEchoes = \cache()->get('user-echoes'.$target->id);
            if (! $receiverEchoes || ! \is_array($receiverEchoes) || \count($receiverEchoes) < 1) {
                $receiverEchoes = UserEcho::with(['room', 'target', 'bot'])->whereRaw('user_id = ?', [$target->id])->get();
            }
            $receiverListening = false;
            foreach ($receiverEchoes as $se => $receiverEcho) {
                if ($receiverEcho['bot_id'] == $this->bot->id) {
                    $receiverListening = true;
                }
            }
            if (! $receiverListening) {
                $receiverPort = new UserEcho();
                $receiverPort->user_id = $target->id;
                $receiverPort->bot_id = $this->bot->id;
                $receiverPort->save();
                $receiverEchoes = UserEcho::with(['room', 'target', 'bot'])->whereRaw('user_id = ?', [$target->id])->get();
                $receiverDirty = 1;
            }
            if ($receiverDirty == 1) {
                $expiresAt = Carbon::now()->addMinutes(60);
                \cache()->put('user-echoes'.$target->id, $receiverEchoes, $expiresAt);
                \event(new Chatter('echo', $target->id, UserEchoResource::collection($receiverEchoes)));
            }
            $receiverDirty = 0;
            $receiverAudibles = \cache()->get('user-audibles'.$target->id);
            if (! $receiverAudibles || ! \is_array($receiverAudibles) || \count($receiverAudibles) < 1) {
                $receiverAudibles = UserAudible::with(['room', 'target', 'bot'])->whereRaw('user_id = ?', [$target->id])->get();
            }
            $receiverListening = false;
            foreach ($receiverAudibles as $se => $receiverEcho) {
                if ($receiverEcho['bot_id'] == $this->bot->id) {
                    $receiverListening = true;
                }
            }
            if (! $receiverListening) {
                $receiverPort = new UserAudible();
                $receiverPort->user_id = $target->id;
                $receiverPort->bot_id = $this->bot->id;
                $receiverPort->save();
                $receiverAudibles = UserAudible::with(['room', 'target', 'bot'])->whereRaw('user_id = ?', [$target->id])->get();
                $receiverDirty = 1;
            }
            if ($receiverDirty == 1) {
                $expiresAt = Carbon::now()->addMinutes(60);
                \cache()->put('user-audibles'.$target->id, $receiverAudibles, $expiresAt);
                \event(new Chatter('audible', $target->id, UserAudibleResource::collection($receiverAudibles)));
            }
            if ($txt != '') {
                $roomId = 0;
                $message = $this->chatRepository->privateMessage($target->id, $roomId, $message, 1, $this->bot->id);
                $message = $this->chatRepository->privateMessage(1, $roomId, $txt, $target->id, $this->bot->id);
            }

            return \response('success');
        }
        if ($type === 'echo') {
            if ($txt != '') {
                $roomId = 0;
                $message = $this->chatRepository->botMessage($this->bot->id, $roomId, $txt, $target->id);
            }

            return \response('success');
        }

        if ($type === 'public') {
            if ($txt != '') {
                $dumproom = $this->chatRepository->message($target->id, $target->chatroom->id, $message, null, null);
                $dumproom = $this->chatRepository->message(1, $target->chatroom->id, $txt, null, $this->bot->id);
            }

            return \response('success');
        }

        return true;
    }
}
