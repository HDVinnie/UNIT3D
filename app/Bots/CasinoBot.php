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
use App\Models\Bot;
use App\Models\BotTransaction;
use App\Models\User;
use App\Models\UserAudible;
use App\Models\UserEcho;
use App\Repositories\ChatRepository;
use Carbon\Carbon;

class CasinoBot
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
        $bot = Bot::where('id', '=', '3')->firstOrFail();
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
        $output = \implode((array) $note, ' ');
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
        $donations = \cache()->get('casinobot-donations');
        if (! $donations) {
            $donations = BotTransaction::with('user', 'bot')->where('bot_id', '=', $this->bot->id)->where('to_bot', '=', 1)->latest()->limit(10)->get();
            \cache()->put('casinobot-donations', $donations, $this->expiresAt);
        }
        $donationDump = '';
        $i = 1;
        foreach ($donations as $donation) {
            $donationDump .= '#'.$i.'. '.$donation->user->username.' sent '.$donation->cost.' '.$donation->forHumans().' with note: '.$donation->comment.".\n";
            $i++;
        }

        return "The Most Recent Donations To Me Are As Follows:\n\n".\trim($donationDump);
    }

    /**
     * Get Help.
     */
    public function getHelp()
    {
        return $this->replaceVars($this->bot->help);
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
            if ($command[$x] === 'donations') {
                $log = $this->getDonations($params);
            }
            if ($command[$x] === 'donate') {
                $log = $this->putDonate($params, $wildcard);
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
