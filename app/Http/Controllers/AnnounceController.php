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
 * @credits    Rhilip <https://github.com/Rhilip>
 */

namespace App\Http\Controllers;

use App\Exceptions\TrackerException;
use App\Helpers\Bencode;
use App\Jobs\ProcessBasicAnnounceRequest;
use App\Jobs\ProcessCompletedAnnounceRequest;
use App\Jobs\ProcessStartedAnnounceRequest;
use App\Jobs\ProcessStoppedAnnounceRequest;
use App\Models\Group;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnnounceController extends Controller
{
    // Torrent Moderation Codes
    protected const PENDING = 0;
    protected const REJECTED = 2;
    protected const POSTPONED = 3;

    // Announce Intervals
    private const MIN = 2_400;
    private const MAX = 3_600;

    // Port Blacklist
    private const BLACK_PORTS = [
        22,  // SSH Port
        53,  // DNS queries
        80, 81, 8080, 8081,  // Hyper Text Transfer Protocol (HTTP) - port used for web traffic
        411, 412, 413,  // 	Direct Connect Hub (unofficial)
        443,  // HTTPS / SSL - encrypted web traffic, also used for VPN tunnels over HTTPS.
        1214,  // Kazaa - peer-to-peer file sharing, some known vulnerabilities, and at least one worm (Benjamin) targeting it.
        3389,  // IANA registered for Microsoft WBT Server, used for Windows Remote Desktop and Remote Assistance connections
        4662,  // eDonkey 2000 P2P file sharing service. http://www.edonkey2000.com/
        6346, 6347,  // Gnutella (FrostWire, Limewire, Shareaza, etc.), BearShare file sharing app
        6699,  // Port used by p2p software, such as WinMX, Napster.
    ];

    /**
     * Announce Code.
     *
     *
     * @throws \Exception
     */
    public function index(Request $request, User $passkey): \Illuminate\Http\Response | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            /**
             * Check client.
             */
            $this->checkClient($request);

            /**
             * Check passkey.
             */
            $this->checkPasskey($passkey);

            /**
             * Check and then get Announce queries.
             */
            $queries = $this->checkAnnounceFields($request);

            /**
             * Check user via supplied passkey.
             */
            $user = $this->checkUser($passkey, $queries);

            /**
             * Get Torrent Info Array from queries and judge if user can reach it.
             */
            $torrent = $this->checkTorrent($queries['info_hash']);

            /**
             * Lock Min Announce Interval.
             */
            $this->checkMinInterval($queries, $user);

            /**
             * Check User Max Connections Per Torrent.
             */
            $this->checkMaxConnections($torrent, $user);

            /**
             * Check Download Slots.
             */
            //$this->checkDownloadSlots($user);

            /**
             * Generate A Response For The Torrent Clent.
             */
            $repDict = $this->generateSuccessAnnounceResponse($queries, $torrent, $user);

            /**
             * Dispatch The Specfic Annnounce Event Job.
             */
            $this->sendAnnounceJob($queries, $user, $torrent);
        } catch (TrackerException $exception) {
            $repDict = $this->generateFailedAnnounceResponse($exception);
        } finally {
            return $this->sendFinalAnnounceResponse($repDict);
        }
    }

    /**
     * @throws \App\Exceptions\TrackerException
     */
    protected function checkClient(Request $request): void
    {
        // Miss Header User-Agent is not allowed.
        if (! $request->header('User-Agent')) {
            throw new TrackerException(120);
        }

        // Block Other Browser, Crawler (May Cheater or Faker Client) by check Requests headers
        if ($request->header('accept-language') || $request->header('referer')
            || $request->header('accept-charset')

            /**
             * This header check may block Non-bittorrent client `Aria2` to access tracker,
             * Because they always add this header which other clients don't have.
             *
             * @see https://blog.rhilip.info/archives/1010/ ( in Chinese )
             */
            || $request->header('want-digest')
        ) {
            throw new TrackerException(122);
        }

        $userAgent = $request->header('User-Agent');

        // Should also block User-Agent strings that are to long. (For Database reasons)
        if (\strlen($userAgent) > 64) {
            throw new TrackerException(123);
        }

        // Block Browser by checking it's User-Agent
        if (\preg_match('/(Mozilla|Browser|Chrome|Safari|AppleWebKit|Opera|Links|Lynx|Bot|Unknown)/i', $userAgent)) {
            throw new TrackerException(121);
        }

        // Block Blacklisted Clients
        if (\in_array($request->header('User-Agent'), \config('client-blacklist.clients'))) {
            throw new TrackerException(128, [':ua' => $request->header('User-Agent')]);
        }
    }

    /** Check Passkey Exist and Valid.
     *
     * @param $passkey
     *
     * @throws \App\Exceptions\TrackerException
     */
    protected function checkPasskey($passkey): void
    {
        // If Passkey Is Not Provided Return Error to Client
        if ($passkey === null) {
            throw new TrackerException(130, [':attribute' => 'passkey']);
        }

        // If Passkey Lenght Is Wrong
        if (\strlen($passkey) !== 32) {
            throw new TrackerException(132, [':attribute' => 'passkey', ':rule' => 32]);
        }

        // If Passkey Format Is Wrong
        if (\strspn(\strtolower($passkey), 'abcdef0123456789') !== 32) {  // MD5 char limit
            throw new TrackerException(131, [':attribute' => 'passkey', ':reason' => 'The format of passkey isnt correct']);
        }
    }

    /**
     * @throws \App\Exceptions\TrackerException
     */
    private function checkAnnounceFields(Request $request): array
    {
        $queries = [
            'timestamp' => $request->server->get('REQUEST_TIME_FLOAT'),
        ];

        // Part.1 check Announce **Need** Fields
        foreach (['info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left'] as $item) {
            $itemData = $request->query->get($item);
            if (! \is_null($itemData)) {
                $queries[$item] = $itemData;
            } else {
                throw new TrackerException(130, [':attribute' => $item]);
            }
        }

        foreach (['info_hash', 'peer_id'] as $item) {
            if (\strlen($queries[$item]) !== 20) {
                throw new TrackerException(133, [':attribute' => $item, ':rule' => 20]);
            }
        }

        foreach (['uploaded', 'downloaded', 'left'] as $item) {
            $itemData = $queries[$item];
            if (! \is_numeric($itemData) || $itemData < 0) {
                throw new TrackerException(134, [':attribute' => $item]);
            }
        }

        // Part.2 check Announce **Option** Fields
        foreach (['event' => '', 'no_peer_id' => 1, 'compact' => 0, 'numwant' => 50, 'corrupt' => 0, 'key' => ''] as $item => $value) {
            $queries[$item] = $request->query->get($item, $value);
        }

        foreach (['numwant', 'corrupt', 'no_peer_id', 'compact'] as $item) {
            if (! \is_numeric($queries[$item]) || $queries[$item] < 0) {
                throw new TrackerException(134, [':attribute' => $item]);
            }
        }

        if (! \in_array(\strtolower($queries['event']), ['started', 'completed', 'stopped', 'paused', ''])) {
            throw new TrackerException(136, [':event' => \strtolower($queries['event'])]);
        }

        // Part.3 check Port is Valid and Allowed
        /**
         * Normally , the port must in 1 - 65535 , that is ( $port > 0 && $port < 0xffff )
         * However, in some case , When `&event=stopped` the port may set to 0.
         */
        if ($queries['port'] === 0 && \strtolower($queries['event']) !== 'stopped') {
            throw new TrackerException(137, [':event' => \strtolower($queries['event'])]);
        }

        if (! \is_numeric($queries['port']) || $queries['port'] < 0 || $queries['port'] > 0xffff || \in_array($queries['port'], self::BLACK_PORTS,
                true)) {
            throw new TrackerException(135, [':port' => $queries['port']]);
        }

        // Part.4 Get User Ip Address
        $queries['ip-address'] = $request->getClientIp();

        // Part.5 Get Users Agent
        $queries['user-agent'] = $request->headers->get('user-agent');

        // Part.6 bin2hex info_hash
        $queries['info_hash'] = \bin2hex($queries['info_hash']);

        // Part.7 bin2hex peer_id
        $queries['peer_id'] = \bin2hex($queries['peer_id']);

        return $queries;
    }

    /** Get User Via Validated Passkey.
     *
     * @param $passkey
     * @param $queries
     *
     * @throws \App\Exceptions\TrackerException
     *
     * @return object
     */
    protected function checkUser($passkey, $queries): object
    {
        // Caached System Required Groups
        $bannedGroup = \cache()->rememberForever('banned_group', fn () => Group::where('slug', '=', 'banned')->pluck('id'));
        $validatingGroup = \cache()->rememberForever('validating_group', fn () => Group::where('slug', '=', 'validating')->pluck('id'));
        $disabledGroup = \cache()->rememberForever('disabled_group', fn () => Group::where('slug', '=', 'disabled')->pluck('id'));

        // Check Passkey Against Users Table
        $user = User::where('passkey', '=', $passkey)->first();

        // If User Doesn't Exist Return Error to Client
        if ($user === null) {
            throw new TrackerException(140);
        }

        // If User Account Is Unactivated/Validating Return Error to Client
        if ($user->active === 0 || $user->group_id === $validatingGroup[0]) {
            throw new TrackerException(141, [':status' => 'Unactivated/Validating']);
        }

        // If User Download Rights Are Disabled Return Error to Client
        if ($user->can_download === 0 && $queries['left'] !== '0') {
            throw new TrackerException(142);
        }

        // If User Is Banned Return Error to Client
        if ($user->group_id === $bannedGroup[0]) {
            throw new TrackerException(141, [':status' => 'Banned']);
        }

        // If User Is Disabled Return Error to Client
        if ($user->group_id === $disabledGroup[0]) {
            throw new TrackerException(141, [':status' => 'Disabled']);
        }

        return $user;
    }

    /**
     * @param $infoHash
     *
     * @throws \App\Exceptions\TrackerException
     *
     * @return object
     */
    protected function checkTorrent($infoHash): object
    {
        // Check Info Hash Against Torrents Table
        $torrent = Torrent::withAnyStatus()
                ->where('info_hash', '=', $infoHash)
                ->first();

        // If Torrent Doesnt Exsist Return Error to Client
        if ($torrent === null) {
            throw new TrackerException(150);
        }

        // If Torrent Is Pending Moderation Return Error to Client
        if ($torrent->status === self::PENDING) {
            throw new TrackerException(151, [':status' => 'PENDING Moderation']);
        }

        // If Torrent Is Rejected Return Error to Client
        if ($torrent->status === self::REJECTED) {
            throw new TrackerException(151, [':status' => 'REJECTED Moderation']);
        }

        // If Torrent Is Postponed Return Error to Client
        if ($torrent->status === self::POSTPONED) {
            throw new TrackerException(151, [':status' => 'POSTPONED Moderation']);
        }

        return $torrent;
    }

    /**
     * @param $queries
     * @param $user
     *
     * @throws \App\Exceptions\TrackerException
     */
    private function checkMinInterval($queries, $user): void
    {
        $prevAnnounce = Peer::where('info_hash', '=', $queries['info_hash'])
            ->where('peer_id', '=', $queries['peer_id'])
            ->where('user_id', '=', $user->id)
            ->pluck('updated_at');

        $carbon = new Carbon();
        if ($prevAnnounce < $carbon->copy()->subSeconds(self::MIN)->toDateTimeString() && \strtolower($queries['event']) !== 'completed') {
            throw new TrackerException(162, [':min' => self::MIN]);
        }
    }

    /**
     * @param $torrent
     * @param $user
     *
     * @throws \App\Exceptions\TrackerException
     */
    private function checkMaxConnections($torrent, $user): void
    {
        // Pull Count On Users Peers Per Torrent For Rate Limiting
        $connections = Peer::where('torrent_id', '=', $torrent->id)->where('user_id', '=', $user->id)->count();

        // If Users Peer Count On A Single Torrent Is Greater Than X Return Error to Client
        if ($connections > \config('announce.rate_limit')) {
            throw new TrackerException(138, [':limit' => \config('announce.rate_limit')]);
        }
    }

    /**
     * @param $user
     *
     * @throws \App\Exceptions\TrackerException
     */
    private function checkDownloadSlots($user): void
    {
        if (\config('announce.slots_system.enabled')) {
            $max = $user->group()->download_slots;

            if ($max > 0) {
                $count = Peer::where('user_id', '=', $user->id)->where('seeder', '=', 0)->count();
                if ($count >= $max) {
                    throw new TrackerException(164, [':max' => $max]);
                }
            }
        }
    }

    /**
     * @param $queries
     * @param $torrent
     * @param $user
     *
     * @return array
     */
    private function generateSuccessAnnounceResponse($queries, $torrent, $user): array
    {
        // Build Response For Bittorrent Client
        $repDict = [
            'interval'     => \rand(self::MIN, self::MAX),
            'min interval' => self::MIN,
            'complete'     => $torrent->seeders,
            'incomplete'   => $torrent->leechers,
        ];

        /**
         * For non `stopped` event only
         * We query peers from database and send peerlist, otherwise just quick return.
         */
        if (\strtolower($queries['event']) !== 'stopped') {
            $limit = ($queries['numwant'] <= 50 ? $queries['numwant'] : 50);

            // Get Torrents Peers
            $peers = Peer::where('torrent_id', '=', $torrent->id)->where('user_id', '!=', $user->id)->take($limit)->get()->toArray();

            $repDict['peers'] = $this->givePeers($peers, $queries['compact'], $queries['no_peer_id']);
            $repDict['peers6'] = $this->givePeers($peers, $queries['compact'], $queries['no_peer_id'], FILTER_FLAG_IPV6);
        }

        return $repDict;
    }

    /**
     * @param $queries
     * @param $user
     * @param $torrent
     *
     * TODO: Paused Event (http://www.bittorrent.org/beps/bep_0021.html)
     */
    private function sendAnnounceJob($queries, $user, $torrent): void
    {
        if (\strtolower($queries['event']) === 'started') {
            ProcessStartedAnnounceRequest::dispatch($queries, $user, $torrent);
        } elseif (\strtolower($queries['event']) === 'completed') {
            ProcessCompletedAnnounceRequest::dispatch($queries, $user, $torrent);
        } elseif (\strtolower($queries['event']) === 'stopped') {
            ProcessStoppedAnnounceRequest::dispatch($queries, $user, $torrent);
        } else {
            ProcessBasicAnnounceRequest::dispatch($queries, $user, $torrent);
        }
    }

    protected function generateFailedAnnounceResponse(TrackerException $trackerException): array
    {
        return [
            'failure reason' => $trackerException->getMessage(),
            'min interval'   => self::MIN,
        /**
         * BEP 31: Failure Retry Extension.
         *
         * However most bittorrent client don't support it, so this feature is disabled default
         *  - libtorrent-rasterbar (e.g. qBittorrent, Deluge )
         *    This library will obey the `min interval` key if exist or it will retry in 60s (By default `min interval`)
         *  - libtransmission (e.g. Transmission )
         *    This library will ignore any other key if failed
         *
         * @see http://www.bittorrent.org/beps/bep_0031.html
         */
            //'retry in' => self::MIN
        ];
    }

    /**
     * @param $repDict
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function sendFinalAnnounceResponse($repDict): \Illuminate\Contracts\Routing\ResponseFactory | \Illuminate\Http\Response
    {
        return \response(Bencode::bencode($repDict))
            ->withHeaders(['Content-Type' => 'text/plain; charset=utf-8'])
            ->withHeaders(['Connection' => 'close'])
            ->withHeaders(['Pragma' => 'no-cache']);
    }

    /**
     * @param     $peers
     * @param     $compact
     * @param     $noPeerId
     * @param int $filterFlag
     *
     * @return string|array
     */
    private function givePeers($peers, $compact, $noPeerId, int $filterFlag = FILTER_FLAG_IPV4): string | array
    {
        if ($compact) {
            $pcomp = '';
            foreach ($peers as $p) {
                if (isset($p['ip'], $p['port']) && \filter_var($p['ip'], FILTER_VALIDATE_IP, $filterFlag)) {
                    $pcomp .= \inet_pton($p['ip']);
                    $pcomp .= \pack('n', (int) $p['port']);
                }
            }

            return $pcomp;
        }
        if ($noPeerId) {
            foreach ($peers as &$p) {
                unset($p['peer_id']);
            }

            return $peers;
        }

        return $peers;
    }
}
