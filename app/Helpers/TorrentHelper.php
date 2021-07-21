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

namespace App\Helpers;

use App\Achievements\UserMade100Uploads;
use App\Achievements\UserMade200Uploads;
use App\Achievements\UserMade25Uploads;
use App\Achievements\UserMade300Uploads;
use App\Achievements\UserMade400Uploads;
use App\Achievements\UserMade500Uploads;
use App\Achievements\UserMade50Uploads;
use App\Achievements\UserMade600Uploads;
use App\Achievements\UserMade700Uploads;
use App\Achievements\UserMade800Uploads;
use App\Achievements\UserMade900Uploads;
use App\Achievements\UserMadeUpload;
use App\Bots\IRCAnnounceBot;
use App\Models\Follow;
use App\Models\PrivateMessage;
use App\Models\Torrent;
use App\Models\User;
use App\Models\Wish;
use App\Notifications\NewUpload;
use Carbon\Carbon;

class TorrentHelper
{
    public static function approveHelper($id): void
    {
        $appurl = \config('app.url');
        $appname = \config('app.name');

        Torrent::approve($id);
        $torrent = Torrent::with('uploader')->withAnyStatus()->where('id', '=', $id)->first();
        $torrent->created_at = Carbon::now();
        $torrent->bumped_at = Carbon::now();
        $torrent->save();

        $uploader = $torrent->uploader;

        $wishes = Wish::where('tmdb', '=', $torrent->tmdb)->whereNull('source')->get();
        if ($wishes) {
            foreach ($wishes as $wish) {
                $wish->source = \sprintf('%s/torrents/%s', $appurl, $torrent->id);
                $wish->save();

                // Send Private Message
                $pm = new PrivateMessage();
                $pm->sender_id = 1;
                $pm->receiver_id = $wish->user_id;
                $pm->subject = 'Wish List Notice!';
                $pm->message = \sprintf('The following item, %s, from your wishlist has been uploaded to %s! You can view it [url=%s/torrents/', $wish->title, $appname, $appurl).$torrent->id.'] HERE [/url]
                                [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
                $pm->save();
            }
        }

        if ($torrent->anon == 0) {
            $followers = Follow::where('target_id', '=', $torrent->user_id)->get();
            if ($followers) {
                foreach ($followers as $follower) {
                    $pushto = User::with('notification')->find($follower->user_id);
                    if ($pushto->acceptsNotification($uploader, $pushto, 'following', 'show_following_upload')) {
                        $pushto->notify(new NewUpload('follower', $torrent));
                    }
                }
            }
        }

        $user = $torrent->user;
        $username = $user->username;
        $anon = $torrent->anon;

        if ($anon == 0) {
            // Achievements
            $user->unlock(new UserMadeUpload(), 1);
            $user->addProgress(new UserMade25Uploads(), 1);
            $user->addProgress(new UserMade50Uploads(), 1);
            $user->addProgress(new UserMade100Uploads(), 1);
            $user->addProgress(new UserMade200Uploads(), 1);
            $user->addProgress(new UserMade300Uploads(), 1);
            $user->addProgress(new UserMade400Uploads(), 1);
            $user->addProgress(new UserMade500Uploads(), 1);
            $user->addProgress(new UserMade600Uploads(), 1);
            $user->addProgress(new UserMade700Uploads(), 1);
            $user->addProgress(new UserMade800Uploads(), 1);
            $user->addProgress(new UserMade900Uploads(), 1);
        }

        // Announce To IRC
        if (\config('irc-bot.enabled') == true) {
            $appname = \config('app.name');
            $ircAnnounceBot = new IRCAnnounceBot();
            if ($anon == 0) {
                $ircAnnounceBot->message(\config('irc-bot.channel'), '['.$appname.'] User '.$username.' has uploaded '.$torrent->name.' grab it now!');
                $ircAnnounceBot->message(\config('irc-bot.channel'), '[Category: '.$torrent->category->name.'] [Type: '.$torrent->type->name.'] [Size:'.$torrent->getSize().']');
            } else {
                $ircAnnounceBot->message(\config('irc-bot.channel'), '['.$appname.'] An anonymous user has uploaded '.$torrent->name.' grab it now!');
                $ircAnnounceBot->message(\config('irc-bot.channel'), '[Category: '.$torrent->category->name.'] [Type: '.$torrent->type->name.'] [Size: '.$torrent->getSize().']');
            }
            $ircAnnounceBot->message(\config('irc-bot.channel'), \sprintf('[Link: %s/torrents/', $appurl).$id.']');
        }
    }
}
