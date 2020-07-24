<?php
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

use App\Helpers\Bencode;
use App\Models\Ban;
use App\Models\BonTransactions;
use App\Models\Follow;
use App\Models\Graveyard;
use App\Models\Group;
use App\Models\History;
use App\Models\Invite;
use App\Models\Peer;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Torrent;
use App\Models\TorrentRequest;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserPrivacy;
use App\Models\Warning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Image;
use ZipArchive;

class UserController extends Controller
{
    /**
     * Show A User.
     *
     * @param \App\Models\User $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($username)
    {
        $user = User::with(['privacy', 'history', 'authentications'])->where('username', '=', $username)->firstOrFail();

        $groups = Group::all();
        $followers = Follow::where('target_id', '=', $user->id)->latest()->limit(25)->get();
        $history = $user->history;
        $warnings = Warning::where('user_id', '=', $user->id)->whereNotNull('torrent')->where('active', '=', 1)->take(config('hitrun.max_warnings'))->get();
        $hitrun = Warning::where('user_id', '=', $user->id)->latest()->paginate(10);

        $bonupload = BonTransactions::where('sender', '=', $user->id)->where([['name', 'like', '%Upload%']])->sum('cost');
        $bondownload = BonTransactions::where('sender', '=', $user->id)->where([['name', 'like', '%Download%']])->sum('cost');

        $realupload = $user->uploaded - $bonupload;
        $realdownload = $user->downloaded + $bondownload;

        $invitedBy = Invite::where('accepted_by', '=', $user->id)->first();

        $requested = TorrentRequest::where('user_id', '=', $user->id)->count();
        $filled = TorrentRequest::where('filled_by', '=', $user->id)->whereNotNull('approved_by')->count();

        return view('user.profile', [
            'route'        => 'profile',
            'user'         => $user,
            'groups'       => $groups,
            'followers'    => $followers,
            'history'      => $history,
            'warnings'     => $warnings,
            'hitrun'       => $hitrun,
            'bonupload'    => $bonupload,
            'realupload'   => $realupload,
            'bondownload'  => $bondownload,
            'realdownload' => $realdownload,
            'requested'    => $requested,
            'filled'       => $filled,
            'invitedBy'    => $invitedBy,
        ]);
    }

    /**
     * User Followers.
     *
     * @param \App\Models\User $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followers($username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $results = Follow::with('user')->where('target_id', '=', $user->id)->latest()->paginate(25);

        return view('user.followers', [
            'route'   => 'follower',
            'results' => $results,
            'user'    => $user,
        ]);
    }

    /**
     * User Topics.
     *
     * @param \App\Models\User $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function topics($username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $results = Topic::where('topics.first_post_user_id', '=', $user->id)->latest()->paginate(25);

        return view('user.topics', [
            'route'   => 'forum',
            'results' => $results,
            'user'    => $user,
        ]);
    }

    /**
     * User Posts.
     *
     * @param \App\Models\User $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function posts($username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        $results = Post::selectRaw('posts.id as id,posts.*')->with(['topic', 'user'])->leftJoin('topics', 'posts.topic_id', '=', 'topics.id')->where('posts.user_id', '=', $user->id)->orderBy('posts.created_at', 'desc')->paginate(25);

        return view('user.posts', [
            'route'   => 'forum',
            'results' => $results,
            'user'    => $user,
        ]);
    }

    /**
     * Edit Profile Form.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editProfileForm(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        return view('user.edit_profile', ['user' => $user, 'route' => 'edit']);
    }

    /**
     * Edit User Profile.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editProfile(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        // Avatar
        $max_upload = config('image.max_upload_size');
        if ($request->hasFile('image') && $request->file('image')->getError() === 0) {
            $image = $request->file('image');
            if (in_array($image->getClientOriginalExtension(), ['jpg', 'JPG', 'jpeg', 'bmp', 'png', 'PNG', 'tiff', 'gif']) && preg_match('#image/*#', $image->getMimeType())) {
                if ($max_upload >= $image->getSize()) {
                    $filename = $user->username.'.'.$image->getClientOriginalExtension();
                    $path = public_path('/files/img/'.$filename);
                    if ($image->getClientOriginalExtension() !== 'gif') {
                        Image::make($image->getRealPath())->fit(150, 150)->encode('png', 100)->save($path);
                    } else {
                        $v = validator($request->all(), [
                            'image' => 'dimensions:ratio=1/1',
                        ]);
                        if ($v->passes()) {
                            $image->move(public_path('/files/img/'), $filename);
                        } else {
                            return redirect()->route('users.show', ['username' => $user->username])
                                ->withErrors('Because you are uploading a GIF, your avatar must be square!');
                        }
                    }
                    $user->image = $user->username.'.'.$image->getClientOriginalExtension();
                } else {
                    return redirect()->route('users.show', ['username' => $user->username])
                        ->withErrors('Your avatar is too large, max file size: '.($max_upload / 1_000_000).' MB');
                }
            }
        }
        // Define data
        $user->title = $request->input('title');
        $user->about = $request->input('about');
        $user->signature = $request->input('signature');
        $user->save();

        return redirect()->route('user_edit_profile_form', ['username' => $user->username])
            ->withSuccess('Your Account Was Updated Successfully!');
    }

    /**
     * User Account Settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settings(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        return view('user.settings', ['user' => $user, 'route' => 'settings']);
    }

    /**
     * Change User Account Settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeSettings(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        // General Settings
        $user->censor = $request->input('censor');
        $user->chat_hidden = $request->input('chat_hidden');

        // Style Settings
        $user->style = (int) $request->input('theme');
        $css_url = $request->input('custom_css');
        if (isset($css_url) && ! filter_var($css_url, FILTER_VALIDATE_URL)) {
            return redirect()->route('users.show', ['username' => $user->username])
                ->withErrors('The URL for the external CSS stylesheet is invalid, try it again with a valid URL.');
        }
        $user->custom_css = $css_url;
        $user->nav = $request->input('sidenav');

        // Torrent Settings
        $user->torrent_layout = (int) $request->input('torrent_layout');
        $user->show_poster = $request->input('show_poster');
        $user->ratings = $request->input('ratings');
        $user->save();

        return redirect()->route('user_settings', ['username' => $user->username])
            ->withSuccess('Your Account Was Updated Successfully!');
    }

    /**
     * User Security Settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function security(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        return view('user.security', ['user' => $user]);
    }

    /**
     * User TwoStep Auth.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeTwoStep(Request $request)
    {
        $user = auth()->user();

        abort_unless(config('auth.TwoStepEnabled') == true, 403);
        $user->twostep = $request->input('twostep');
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Changed Your TwoStep Auth Status!');
    }

    /**
     * User Password Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changePassword(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $v = validator($request->all(), [
            'current_password'          => 'required',
            'new_password'              => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);
        if ($v->passes()) {
            if (Hash::check($request->input('current_password'), $user->password)) {
                $user->password = Hash::make($request->input('new_password'));
                $user->save();

                return redirect()->route('home.index')->withSuccess('Your Password Has Been Reset');
            }

            return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#password'])
                ->withErrors('Your Password Was Incorrect!');
        }

        return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#password'])
                ->withErrors('Your New Password Is To Weak!');
    }

    /**
     * User Email Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeEmail(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        if (config('email-blacklist.enabled') == true) {
            $v = validator($request->all(), [
                'email' => 'required|string|email|max:70|blacklist|unique:users',
            ]);
        } else {
            $v = validator($request->all(), [
                'email' => 'required|string|email|max:70|unique:users',
            ]);
        }

        if ($v->fails()) {
            return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#email'])
                ->withErrors($v->errors());
        }
        $user->email = $request->input('email');
        $user->save();

        return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#email'])
            ->withSuccess('Your Email Was Updated Successfully!');
    }

    /**
     * Change User Privacy Level.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makePrivate(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->private_profile = 1;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Gone Private!');
    }

    /**
     * Change User Privacy Level.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makePublic(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->private_profile = 0;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Gone Public!');
    }

    /**
     * Change User Notification Setting.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableNotifications(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->block_notifications = 1;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Disabled Notifications!');
    }

    /**
     * Change User Notification Setting.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableNotifications(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->block_notifications = 0;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Enabled Notifications!');
    }

    /**
     * Change User Hidden Value.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeHidden(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->hidden = 1;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Disappeared Like A Ninja!');
    }

    /**
     * Change User Hidden Value.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeVisible(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->hidden = 0;
        $user->save();

        return redirect()->route('users.show', ['username' => $user->username])
            ->withSuccess('You Have Given Up Your Ninja Ways And Become Visible!');
    }

    /**
     * Change User PID.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePID(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->passkey = md5(uniqid().time().microtime());
        $user->save();

        return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#pid'])
            ->withSuccess('Your PID Was Changed Successfully!');
    }

    /**
     * User Other Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeOther(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_other_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_online = ($request->input('show_online') && $request->input('show_online') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#other'])
            ->withSuccess('Your Other Privacy Settings Have Been Saved!');
    }

    /**
     * User Request Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeRequest(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_request_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_requested = ($request->input('show_requested') && $request->input('show_requested') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#request'])
            ->withSuccess('Your Request Privacy Settings Have Been Saved!');
    }

    /**
     * User Achievement Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeAchievement(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_achievement_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_achievement = ($request->input('show_achievement') && $request->input('show_achievement') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#achievement'])
            ->withSuccess('Your Achievement Privacy Settings Have Been Saved!');
    }

    /**
     * User Forum Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeForum(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_forum_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_topic = ($request->input('show_topic') && $request->input('show_topic') == 1 ? 1 : 0);
        $privacy->show_post = ($request->input('show_post') && $request->input('show_post') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#forum'])
            ->withSuccess('Your Forum History Privacy Settings Have Been Saved!');
    }

    /**
     * User Follower Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeFollower(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_follower_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_follower = ($request->input('show_follower') && $request->input('show_follower') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#follower'])
            ->withSuccess('Your Follower Privacy Settings Have Been Saved!');
    }

    /**
     * User Torrent Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeTorrent(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }
        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_torrent_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_upload = ($request->input('show_upload') && $request->input('show_upload') == 1 ? 1 : 0);
        $privacy->show_download = ($request->input('show_download') && $request->input('show_download') == 1 ? 1 : 0);
        $privacy->show_peer = ($request->input('show_peer') && $request->input('show_peer') == 1 ? 1 : 0);
        $privacy->save();

        $user->peer_hidden = 0;
        $user->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#torrent'])
            ->withSuccess('Your Torrent History Privacy Settings Have Been Saved!');
    }

    /**
     * User Account Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeAccountNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_account_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_account_follow = ($request->input('show_account_follow') && $request->input('show_account_follow') == 1 ? 1 : 0);
        $notification->show_account_unfollow = ($request->input('show_account_unfollow') && $request->input('show_account_unfollow') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#account'])
            ->withSuccess('Your Account Notification Settings Have Been Saved!');
    }

    /**
     * User Following Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeFollowingNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_following_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_following_upload = ($request->input('show_following_upload') && $request->input('show_following_upload') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#following'])
            ->withSuccess('Your Followed User Notification Settings Have Been Saved!');
    }

    /**
     * User BON Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeBonNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_bon_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_bon_gift = ($request->input('show_bon_gift') && $request->input('show_bon_gift') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#bon'])
            ->withSuccess('Your BON Notification Settings Have Been Saved!');
    }

    /**
     * User Subscription Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeSubscriptionNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_subscription_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_subscription_forum = ($request->input('show_subscription_forum') && $request->input('show_subscription_forum') == 1 ? 1 : 0);
        $notification->show_subscription_topic = ($request->input('show_subscription_topic') && $request->input('show_subscription_topic') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#subscription'])
            ->withSuccess('Your Subscription Notification Settings Have Been Saved!');
    }

    /**
     * User Request Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeRequestNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_request_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_request_comment = ($request->input('show_request_comment') && $request->input('show_request_comment') == 1 ? 1 : 0);
        $notification->show_request_bounty = ($request->input('show_request_bounty') && $request->input('show_request_bounty') == 1 ? 1 : 0);
        $notification->show_request_fill = ($request->input('show_request_fill') && $request->input('show_request_fill') == 1 ? 1 : 0);
        $notification->show_request_fill_approve = ($request->input('show_request_fill_approve') && $request->input('show_request_fill_approve') == 1 ? 1 : 0);
        $notification->show_request_fill_reject = ($request->input('show_request_fill_reject') && $request->input('show_request_fill_reject') == 1 ? 1 : 0);
        $notification->show_request_claim = ($request->input('show_request_claim') && $request->input('show_request_claim') == 1 ? 1 : 0);
        $notification->show_request_unclaim = ($request->input('show_request_unclaim') && $request->input('show_request_unclaim') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#request'])
            ->withSuccess('Your Request Notification Settings Have Been Saved!');
    }

    /**
     * User Torrent Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeTorrentNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_torrent_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_torrent_comment = ($request->input('show_torrent_comment') && $request->input('show_torrent_comment') == 1 ? 1 : 0);
        $notification->show_torrent_thank = ($request->input('show_torrent_thank') && $request->input('show_torrent_thank') == 1 ? 1 : 0);
        $notification->show_torrent_tip = ($request->input('show_torrent_tip') && $request->input('show_torrent_tip') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#torrent'])
            ->withSuccess('Your Torrent Notification Settings Have Been Saved!');
    }

    /**
     * User Mention Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeMentionNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_mention_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_mention_torrent_comment = ($request->input('show_mention_torrent_comment') && $request->input('show_mention_torrent_comment') == 1 ? 1 : 0);
        $notification->show_mention_request_comment = ($request->input('show_mention_request_comment') && $request->input('show_mention_request_comment') == 1 ? 1 : 0);
        $notification->show_mention_article_comment = ($request->input('show_mention_article_comment') && $request->input('show_mention_article_comment') == 1 ? 1 : 0);
        $notification->show_mention_forum_post = ($request->input('show_mention_forum_post') && $request->input('show_mention_forum_post') == 1 ? 1 : 0);

        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#mention'])
            ->withSuccess('Your @Mention Notification Settings Have Been Saved!');
    }

    /**
     * User Forum Notification Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeForumNotification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $notification = $user->notification;
        if (! $notification) {
            $notification = new UserNotification();
            $notification->setDefaultValues();
            $notification->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $notification->json_forum_groups = array_merge($notification->expected_groups, ['default_groups' => $tomerge]);
        $notification->show_forum_topic = ($request->input('show_forum_topic') && $request->input('show_forum_topic') == 1 ? 1 : 0);
        $notification->save();

        return redirect()->route('user_notification', ['username' => $user->username, 'hash' => '#forum'])
            ->withSuccess('Your Forum Notification Settings Have Been Saved!');
    }

    /**
     * User Profile Privacy Change.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function changeProfile(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $privacy = $user->privacy;
        if (! $privacy) {
            $privacy = new UserPrivacy();
            $privacy->setDefaultValues();
            $privacy->user_id = $user->id;
        }

        $approved = $request->input('approved');
        $groups = Group::all();
        $tomerge = [];
        foreach ($groups as $group) {
            $tomerge[$group->id] = is_array($approved) && in_array($group->id, $approved) ? 1 : 0;
        }
        $privacy->json_profile_groups = array_merge($privacy->expected_groups, ['default_groups' => $tomerge]);
        $privacy->show_profile_torrent_count = ($request->input('show_profile_torrent_count') && $request->input('show_profile_torrent_count') == 1 ? 1 : 0);
        $privacy->show_profile_torrent_ratio = ($request->input('show_profile_torrent_ratio') && $request->input('show_profile_torrent_ratio') == 1 ? 1 : 0);
        $privacy->show_profile_torrent_seed = ($request->input('show_profile_torrent_seed') && $request->input('show_profile_torrent_seed') == 1 ? 1 : 0);
        $privacy->show_profile_torrent_extra = ($request->input('show_profile_torrent_extra') && $request->input('show_profile_torrent_extra') == 1 ? 1 : 0);
        $privacy->show_profile_about = ($request->input('show_profile_about') && $request->input('show_profile_about') == 1 ? 1 : 0);
        $privacy->show_profile_achievement = ($request->input('show_profile_achievement') && $request->input('show_profile_achievement') == 1 ? 1 : 0);
        $privacy->show_profile_badge = ($request->input('show_profile_badge') && $request->input('show_profile_badge') == 1 ? 1 : 0);
        $privacy->show_profile_follower = ($request->input('show_profile_follower') && $request->input('show_profile_follower') == 1 ? 1 : 0);
        $privacy->show_profile_title = ($request->input('show_profile_title') && $request->input('show_profile_title') == 1 ? 1 : 0);
        $privacy->show_profile_bon_extra = ($request->input('show_profile_bon_extra') && $request->input('show_profile_bon_extra') == 1 ? 1 : 0);
        $privacy->show_profile_comment_extra = ($request->input('show_profile_comment_extra') && $request->input('show_profile_comment_extra') == 1 ? 1 : 0);
        $privacy->show_profile_forum_extra = ($request->input('show_profile_forum_extra') && $request->input('show_profile_forum_extra') == 1 ? 1 : 0);
        $privacy->show_profile_request_extra = ($request->input('show_profile_request_extra') && $request->input('show_profile_request_extra') == 1 ? 1 : 0);
        $privacy->show_profile_warning = ($request->input('show_profile_warning') && $request->input('show_profile_warning') == 1 ? 1 : 0);
        $privacy->save();

        return redirect()->route('user_privacy', ['username' => $user->username, 'hash' => '#profile'])
            ->withSuccess('Your Profile Privacy Settings Have Been Saved!');
    }

    /**
     * Change User RID.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeRID(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->rsskey = md5(uniqid().time().microtime());
        $user->save();

        return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#rid'])
            ->withSuccess('Your RID Was Changed Successfully!');
    }

    /**
     * Change User API Token.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeApiToken(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $user->api_token = Str::random(100);
        $user->save();

        return redirect()->route('user_security', ['username' => $user->username, 'hash' => '#api'])
            ->withSuccess('Your API Token Was Changed Successfully!');
    }

    /**
     * User Privacy Settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function privacy(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $groups = Group::where('level', '>', 0)->orderBy('level', 'desc')->get();

        return view('user.privacy', ['user' => $user, 'groups'=> $groups]);
    }

    /**
     * User Notification Settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function notification(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->id == $user->id, 403);

        $groups = Group::where('level', '>', 0)->orderBy('level', 'desc')->get();

        return view('user.notification', ['user' => $user, 'groups'=> $groups]);
    }

    /**
     * Uses Input's To Put Together A Filtered View.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @throws \Throwable
     *
     * @return array
     */
    public function myFilter(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);
        if ($request->has('view') && $request->input('view') == 'seeds') {
            $history = Peer::with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->selectRaw('distinct(torrents.info_hash),max(peers.id) as id,max(torrents.name) as name,max(torrents.seeders) as seeders,max(torrents.leechers) as leechers,max(torrents.times_completed) as times_completed,max(torrents.size) as size,max(history.info_hash) as history_info_hash,max(history.created_at) as history_created_at,max(torrents.id) as torrent_id,max(history.seedtime) as seedtime')->leftJoin('torrents', 'torrents.id', '=', 'peers.torrent_id')->leftJoin('history', 'history.info_hash', '=', 'torrents.info_hash')->where('peers.user_id', '=', $user->id)->whereRaw('history.user_id = ? and history.seeder = ?', [$user->id, 1])
                ->where('peers.seeder', '=', 1)->groupBy('torrents.info_hash');
            $order = null;
            $sorting = null;
            $history->where(function ($query) use ($request) {
                if ($request->has('dying') && $request->input('dying') != null) {
                    $query->orWhereRaw('(torrents.seeders = ? AND torrents.times_completed > ?)', [1, 2]);
                }
                if ($request->has('legendary') && $request->input('legendary') != null) {
                    $query->orWhereRaw('(torrents.created_at < date_sub(now(), interval 12 month))', []);
                }
                if ($request->has('old') && $request->input('old') != null) {
                    $query->orWhereRaw('(torrents.created_at < date_sub(now(), Interval 6 month) and torrents.created_at > date_sub(now(), interval 12 month))', []);
                }
                if ($request->has('huge') && $request->input('huge') != null) {
                    $query->orWhereRaw('(torrents.size > (1073741824 * 100))', []);
                }
                if ($request->has('large') && $request->input('large') != null) {
                    $query->orWhereRaw('(torrents.size > (1073741824 * 25) and torrents.size < (1073741824 * 100))', []);
                }
                if ($request->has('everyday') && $request->input('everyday') != null) {
                    $query->orWhereRaw('(torrents.size > (1073741824) and torrents.size < (1073741824 * 25))', []);
                }
                if ($request->has('participant_seeder') && $request->input('participant_seeder') != null) {
                    $query->orWhereRaw('(history.active = 1 AND history.seedtime > (2592000) and history.seedtime < (2592000 * 2))', []);
                }
                if ($request->has('teamplayer_seeder') && $request->input('teamplayer_seeder') != null) {
                    $query->orWhereRaw('(history.active = 1 AND history.seedtime > (2592000 * 2) and history.seedtime < (2592000 * 3))', []);
                }
                if ($request->has('committed_seeder') && $request->input('committed_seeder') != null) {
                    $query->orWhereRaw('(history.active = 1 AND history.seedtime > (2592000 * 3) and history.seedtime < (2592000 * 6))', []);
                }
                if ($request->has('mvp_seeder') && $request->input('mvp_seeder') != null) {
                    $query->orWhereRaw('(history.active = 1 AND history.seedtime > (2592000 * 6) and history.seedtime < (2592000 * 12))', []);
                }
                if ($request->has('legendary_seeder') && $request->input('legendary_seeder') != null) {
                    $query->orWhereRaw('(history.active = 1 AND history.seedtime > (2592000 * 12))', []);
                }
            });
            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;
            if ($sorting != 'name' && $sorting != 'size' && $sorting != 'times_completed' && $sorting != 'seeders' && $sorting != 'leechers') {
                if ($sorting == 'seedtime') {
                    $table = $history->orderBy($sorting, $order)->paginate(50);
                } elseif ($sorting == 'hcreated_at') {
                    $table = $history->orderBy('history_created_at', $order)->paginate(50);
                } else {
                    $table = $history->orderBy($sorting, $order)->paginate(50);
                }
            } else {
                $table = $history->orderBy($sorting, $order)->paginate(50);
            }

            return view('user.filters.seeds', [
                'user'  => $user,
                'seeds' => $table,
            ])->render();
        }
        if ($request->has('view') && $request->input('view') == 'requests') {
            $torrentRequests = TorrentRequest::with(['user', 'category', 'type']);
            $order = null;
            $sorting = null;
            if ($request->has('name') && $request->input('name') != null) {
                $torrentRequests->where('name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('filled') && $request->input('filled') != null) {
                $torrentRequests->whereNotNull('filled_by')
                    ->whereNotNull('filled_hash')
                    ->whereNotNull('filled_when')
                    ->whereNotNull('approved_by')
                    ->whereNotNull('approved_when');
            }
            if ($request->has('pending') && $request->input('pending') != null) {
                $torrentRequests->whereNotNull('filled_by')
                    ->whereNotNull('filled_hash')
                    ->whereNotNull('filled_when')
                    ->whereNull('approved_by')
                    ->whereNull('approved_when');
            }
            if ($request->has('claimed') && $request->input('claimed') != null) {
                $torrentRequests->where('claimed', '=', 1);
            }
            if ($request->has('unfilled') && $request->input('unfilled') != null) {
                $torrentRequests->where(function ($query) {
                    $query->whereNull('filled_by')->orWhereNull('filled_hash')->orWhereNull('approved_by');
                });
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;
            if ($sorting == 'date') {
                $table = $torrentRequests->where('user_id', '=', $user->id)->orderBy('created_at', $order)->paginate(25);
            } else {
                $table = $torrentRequests->where('user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(25);
            }

            return view('user.filters.requests', [
                'user'            => $user,
                'torrentRequests' => $table,
            ])->render();
        }

        if ($request->has('view') && $request->input('view') == 'resurrections') {
            $history = Graveyard::with(['torrent', 'user'])->leftJoin('torrents', 'torrents.id', '=', 'graveyard.torrent_id');
            $order = null;
            $sorting = null;
            if ($request->has('rewarded') && $request->input('rewarded') != null) {
                $history->where('graveyard.rewarded', '=', 1);
            }
            if ($request->has('notrewarded') && $request->input('notrewarded') != null) {
                $history->where('graveyard.rewarded', '=', 0);
            }
            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;
            if ($sorting != 'name' && $sorting != 'size' && $sorting != 'times_completed' && $sorting != 'seeders' && $sorting != 'leechers') {
                if ($sorting == 'goal') {
                    $table = $history->where('graveyard.user_id', '=', $user->id)->orderBy('graveyard.seedtime', $order)->paginate(50);
                } else {
                    $table = $history->where('graveyard.user_id', '=', $user->id)->orderBy('graveyard.'.$sorting, $order)->paginate(50);
                }
            } else {
                $table = $history->where('graveyard.user_id', '=', $user->id)->orderBy('torrents.'.$sorting, $order)->paginate(50);
            }

            return view('user.filters.resurrections', [
                'user'          => $user,
                'resurrections' => $table,
            ])->render();
        } elseif ($request->has('view') && $request->input('view') == 'active') {
            $history = Peer::with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->leftJoin('torrents', 'torrents.id', '=', 'peers.torrent_id');

            $order = null;
            $sorting = null;

            if ($request->has('seeding') && $request->input('seeding') != null) {
                $history->where('peers.seeder', '=', 1);
            }
            if ($request->has('leeching') && $request->input('leeching') != null) {
                $history->where('peers.seeder', '=', 0)->where('peers.left', '>', 0);
            }
            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;

            if ($sorting != 'name' && $sorting != 'size' && $sorting != 'times_completed' && $sorting != 'seeders' && $sorting != 'leechers') {
                $table = $history->where('peers.user_id', '=', $user->id)->orderBy('peers.'.$sorting, $order)->paginate(50);
            } else {
                $table = $history->where('peers.user_id', '=', $user->id)->orderBy('torrents.'.$sorting, $order)->paginate(50);
            }

            return view('user.filters.active', [
                'user'   => $user,
                'active' => $table,
            ])->render();
        } elseif ($request->has('view') && $request->input('view') == 'unsatisfieds') {
            if (config('hitrun.enabled') == true) {
                $history = History::selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(torrents.name) as name, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seedtime) as satisfied_at, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                    $query->withAnyStatus();
                }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('actual_downloaded', '>', 0)
                    ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.enabled') == true ? (config('hitrun.buffer') / 100) : 0).'))')->groupBy('history.info_hash');
            } else {
                $history = History::selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(torrents.name) as name, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seedtime) as satisfied_at, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                    $query->withAnyStatus();
                }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('actual_downloaded', '>', 0)
                    ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.enabled') == true ? (config('hitrun.buffer') / 100) : 0).'))')->groupBy('history.info_hash');
            }
            $order = null;
            $sorting = null;

            $history->whereRaw('(history.seedtime < ? and history.immune != 1)', [config('hitrun.seedtime')]);

            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;

            if ($request->has('error') && $request->input('error') != null) {
                $history->where('seeder', '=', 0);
            }

            if ($request->has('seeding') && $request->input('seeding') != null) {
                $history->where('seeder', '=', 1);
            }

            if ($sorting != 'name' && $sorting != 'satisfied_at' && $sorting != 'size' && $sorting != 'times_completed' && $sorting != 'seeders' && $sorting != 'leechers') {
                $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);
            } elseif ($sorting == 'satisfied_at') {
                if ($order == 'desc') {
                    $order = 'asc';
                } elseif ($order == 'asc') {
                    $order = 'desc';
                }
                $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);
            } else {
                $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);
            }

            return view('user.filters.unsatisfieds', [
                'user'      => $user,
                'downloads' => $table,
            ])->render();
        } elseif ($request->has('view') && $request->input('view') == 'downloads') {
            $history = History::selectRaw('distinct(history.info_hash), max(history.completed_at) as completed_at, max(torrents.name) as name, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('actual_downloaded', '>', 0)
                ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.enabled') == true ? (config('hitrun.buffer') / 100) : 0).'))')->groupBy('history.info_hash');
            $order = null;
            $sorting = null;

            $history->where(function ($query) use ($request) {
                if ($request->has('satisfied') && $request->input('satisfied') != null) {
                    $query->orWhereRaw('(history.seedtime >= ? or history.immune = 1)', [config('hitrun.seedtime')]);
                }
                if ($request->has('notsatisfied') && $request->input('notsatisfied') != null) {
                    $query->orWhereRaw('(history.seedtime < ? and history.immune != 1)', [config('hitrun.seedtime')]);
                }
            });
            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;

            if ($request->has('completed') && $request->input('completed') != null) {
                $history->where('completed_at', '>', 0);
            }

            if ($request->has('active') && $request->input('active') != null) {
                $history->where('active', '=', 1);
            }

            if ($request->has('seeding') && $request->input('seeding') != null) {
                $history->where('seeder', '=', 1);
            }

            if ($request->has('prewarned') && $request->input('prewarned') != null) {
                $history->where('prewarn', '=', 1);
            }

            if ($request->has('hr') && $request->input('hr') != null) {
                $history->where('hitrun', '=', 1);
            }

            if ($request->has('immune') && $request->input('immune') != null) {
                $history->where('immune', '=', 1);
            }

            if ($sorting != 'name' && $sorting != 'size' && $sorting != 'times_completed' && $sorting != 'seeders' && $sorting != 'leechers') {
                $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);
            } else {
                $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);
            }

            return view('user.filters.downloads', [
                'user'      => $user,
                'downloads' => $table,
            ])->render();
        } elseif ($request->has('view') && $request->input('view') == 'uploads') {
            $history = Torrent::selectRaw('distinct(torrents.id),max(torrents.moderated_at) as moderated_at,max(torrents.slug) as slug,max(torrents.user_id) as user_id,max(torrents.name) as name,max(torrents.category_id) as category_id,max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed,max(torrents.created_at) as created_at,max(torrents.status) as status,count(distinct thanks.id) as thanked_total,max(bt.tipped_total) as tipped_total')->withAnyStatus()->where('torrents.user_id', '=', $user->id)->with(['tips', 'thanks'])->leftJoin(DB::raw('(select distinct(bon_transactions.torrent_id),sum(bon_transactions.cost) as tipped_total from bon_transactions group by bon_transactions.torrent_id) as bt'), 'bt.torrent_id', '=', 'torrents.id')->leftJoin('thanks', 'thanks.torrent_id', 'torrents.id')->groupBy('torrents.id');

            $order = null;
            $sorting = null;

            if ($request->has('pending') && $request->input('pending') != null) {
                $history->whereRaw('(torrents.status) = ?', [0]);
            }
            if ($request->has('approved') && $request->input('approved') != null) {
                $history->whereRaw('(torrents.status) = ?', [1]);
            }
            if ($request->has('rejected') && $request->input('rejected') != null) {
                $history->whereRaw('(torrents.status) = ?', [2]);
            }

            $history->where(function ($query) use ($request) {
                if ($request->has('dead') && $request->input('dead') != null) {
                    $query->orWhereRaw('(torrents.seeders+torrents.leechers) = ?', [0]);
                }
                if ($request->has('alive') && $request->input('alive') != null) {
                    $query->orWhereRaw('torrents.seeders >= ?', [1]);
                }
                if ($request->has('reseed') && $request->input('reseed') != null) {
                    $query->orWhereRaw('(torrents.seeders = ?) AND (torrents.leechers >= ?)', [0, 1]);
                }
                if ($request->has('error') && $request->input('error') != null) {
                    $query->orWhereRaw('(torrents.seeders = ?) AND (torrents.leechers = ?)', [0, 0]);
                }
            });

            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;

            if ($sorting == 'tipped' || $sorting == 'thanked') {
                $table = $history->orderBy($sorting.'_total', $order)->paginate(50);
            } else {
                $table = $history->orderBy($sorting, $order)->paginate(50);
            }

            return view('user.filters.uploads', [
                'user'    => $user,
                'uploads' => $table,
            ])->render();
        } elseif ($request->has('view') && $request->input('view') == 'history') {
            $history = History::with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->selectRaw('distinct(history.id),max(history.info_hash) as info_hash,max(history.agent) as agent,max(history.uploaded) as uploaded,max(history.downloaded) as downloaded,max(history.seeder) as seeder,max(history.active) as active,max(history.actual_uploaded) as actual_uploaded,max(history.actual_downloaded) as actual_downloaded,max(history.seedtime) as seedtime,max(history.created_at) as created_at,max(history.updated_at) as updated_at,max(history.completed_at) as completed_at,max(history.immune) as immune,max(history.hitrun) as hitrun,max(history.prewarn) as prewarn,max(torrents.moderated_at) as moderated_at,max(torrents.slug) as slug,max(torrents.user_id) as user_id,max(torrents.name) as name,max(torrents.category_id) as category_id,max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed,max(torrents.status) as status')->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->groupBy('history.id');

            $order = null;
            $sorting = null;
            if ($request->has('sorting') && $request->input('sorting') != null) {
                $sorting = $request->input('sorting');
            }
            if ($request->has('direction') && $request->input('direction') != null) {
                $order = $request->input('direction');
            }
            if (! $sorting || $sorting == null || ! $order || $order == null) {
                $sorting = 'created_at';
                $order = 'desc';
                // $order = 'asc';
            }
            $direction = $order == 'asc' ? 1 : 2;

            if ($request->has('name') && $request->input('name') != null) {
                $history->where('torrents.name', 'like', '%'.$request->input('name').'%');
            }

            if ($request->has('completed') && $request->input('completed') != null) {
                $history->where('completed_at', '>', 0);
            }

            if ($request->has('active') && $request->input('active') != null) {
                $history->where('active', '=', 1);
            }

            if ($request->has('seeding') && $request->input('seeding') != null) {
                $history->where('seeder', '=', 1);
            }

            if ($request->has('prewarned') && $request->input('prewarned') != null) {
                $history->where('prewarn', '=', 1);
            }

            if ($request->has('hr') && $request->input('hr') != null) {
                $history->where('hitrun', '=', 1);
            }

            if ($request->has('immune') && $request->input('immune') != null) {
                $history->where('immune', '=', 1);
            }

            $table = $history->where('history.user_id', '=', $user->id)->orderBy($sorting, $order)->paginate(50);

            return view('user.filters.history', [
                'user'    => $user,
                'history' => $table,
            ])->render();
        }

        return false;
    }

    /**
     * Get A Users Downloads (Fully Downloaded) Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function downloads(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        if (($request->user()->id == $user->id || $request->user()->group->is_modo)) {
            $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
            $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
            $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
            $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');

            $logger = 'user.private.downloads';

            if (config('hitrun.enabled') == true) {
                $downloads = History::selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                    $query->withAnyStatus();
                }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('actual_downloaded', '>', 0)
                    ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.buffer') / 100).'))')
                    ->where('history.user_id', '=', $user->id)->groupBy('history.info_hash')->orderBy('completed_at', 'desc')
                    ->paginate(50);
            } else {
                $downloads = History::selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                    $query->withAnyStatus();
                }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')
                    ->where('history.user_id', '=', $user->id)->groupBy('history.info_hash')->orderBy('completed_at', 'desc')
                    ->paginate(50);
            }

            return view($logger, [
                'route'         => 'downloads',
                'user'          => $user,
                'downloads'     => $downloads,
                'his_upl'       => $his_upl,
                'his_upl_cre'   => $his_upl_cre,
                'his_downl'     => $his_downl,
                'his_downl_cre' => $his_downl_cre,
            ]);
        }
        $logger = 'user.downloads';
        if (config('hitrun.enabled') == true) {
            $downloads = History::with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('actual_downloaded', '>', 0)
                ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.buffer') / 100).'))')
                ->where('history.user_id', '=', $user->id)
                ->groupBy('history.info_hash')->orderBy('completed_at', 'desc')
                ->paginate(50);
        } else {
            $downloads = History::with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->selectRaw('distinct(history.info_hash), max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')
                ->where('history.user_id', '=', $user->id)
                ->groupBy('history.info_hash')->orderBy('completed_at', 'desc')
                ->paginate(50);
        }

        return view($logger, [
            'route'        => 'downloads',
            'user'         => $user,
            'downloads'    => $downloads,
        ]);
    }

    /**
     * Get A Users Requested Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function requested(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        if (($request->user()->id == $user->id || $request->user()->group->is_modo)) {
            $logger = 'user.private.requests';

            $torrentRequests = TorrentRequest::with(['user', 'category', 'type'])->where('user_id', '=', $user->id)->latest()->paginate(25);

            return view($logger, [
                'route'           => 'requests',
                'user'            => $user,
                'torrentRequests' => $torrentRequests,
            ]);
        }
        $logger = 'user.requests';
        $torrentRequests = TorrentRequest::with(['user', 'category', 'type'])->where('user_id', '=', $user->id)->where('anon', '!=', 1)->latest()->paginate(25);

        return view($logger, [
            'route'           => 'requests',
            'user'            => $user,
            'torrentRequests' => $torrentRequests,
        ]);
    }

    /**
     * Get A Users Unsatisfieds Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function unsatisfieds(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);
        $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
        $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
        $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
        $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');

        if (config('hitrun.enabled') == true) {
            $downloads = History::selectRaw('distinct(history.info_hash), max(torrents.name) as name, max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seedtime) as satisfied_at, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')
                ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.buffer') / 100).'))')
                ->where('history.user_id', '=', $user->id)->groupBy('history.info_hash')->orderBy('satisfied_at', 'desc')
                ->whereRaw('(history.seedtime < ? and history.immune != 1)', [config('hitrun.seedtime')])
                ->paginate(50);
        } else {
            $downloads = History::selectRaw('distinct(history.info_hash), max(torrents.name) as name, max(torrents.id), max(history.completed_at) as completed_at, max(history.created_at) as created_at, max(history.id) as id, max(history.user_id) as user_id, max(history.seedtime) as seedtime, max(history.seedtime) as satisfied_at, max(history.seeder) as seeder, max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed')->with(['torrent' => function ($query) {
                $query->withAnyStatus();
            }])->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')
                ->whereRaw('history.actual_downloaded > (torrents.size * ('.(config('hitrun.buffer') / 100).'))')
                ->where('history.user_id', '=', $user->id)->groupBy('history.info_hash')->orderBy('satisfied_at', 'desc')
                ->whereRaw('(history.seedtime < ? and history.immune != 1)', [config('hitrun.seedtime')])
                ->paginate(50);
        }

        return view('user.private.unsatisfieds', [
            'route'         => 'unsatisfieds',
            'user'          => $user,
            'downloads'     => $downloads,
            'his_upl'       => $his_upl,
            'his_upl_cre'   => $his_upl_cre,
            'his_downl'     => $his_downl,
            'his_downl_cre' => $his_downl_cre,
        ]);
    }

    /**
     * Get A Users History Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function torrents(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);
        $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
        $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
        $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
        $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');
        $history = History::with(['torrent' => function ($query) {
            $query->withAnyStatus();
        }])->selectRaw('distinct(history.id),max(history.info_hash) as info_hash,max(history.agent) as agent,max(history.uploaded) as uploaded,max(history.downloaded) as downloaded,max(history.seeder) as seeder,max(history.active) as active,max(history.actual_uploaded) as actual_uploaded,max(history.actual_downloaded) as actual_downloaded,max(history.seedtime) as seedtime,max(history.created_at) as created_at,max(history.updated_at) as updated_at,max(history.completed_at) as completed_at,max(history.immune) as immune,max(history.hitrun) as hitrun,max(history.prewarn) as prewarn,max(torrents.moderated_at) as moderated_at,max(torrents.slug) as slug,max(torrents.user_id) as user_id,max(torrents.name) as name,max(torrents.category_id) as category_id,max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed,max(torrents.status) as status')->leftJoin('torrents', 'torrents.info_hash', '=', 'history.info_hash')->where('history.user_id', '=', $user->id)->groupBy('history.id')
            ->orderBy('created_at', 'DESC')->paginate(50);

        return view('user.private.torrents', [
            'route'         => 'torrents',
            'user'          => $user,
            'history'       => $history,
            'his_upl'       => $his_upl,
            'his_upl_cre'   => $his_upl_cre,
            'his_downl'     => $his_downl,
            'his_downl_cre' => $his_downl_cre,
        ]);
    }

    /**
     * Get A Users Graveyard Resurrections.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resurrections(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);

        $resurrections = Graveyard::with(['torrent', 'user'])->where('user_id', '=', $user->id)->paginate(50);

        return view('user.private.resurrections', [
            'route'         => 'resurrections',
            'user'          => $user,
            'resurrections' => $resurrections,
        ]);
    }

    /**
     * Get A User Uploads.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function uploads(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();
        if ($request->user()->id == $user->id || $request->user()->group->is_modo) {
            $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
            $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
            $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
            $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');

            $logger = 'user.private.uploads';
            $uploads = Torrent::with(['tips', 'thanks', 'category'])->selectRaw('distinct(torrents.id),max(torrents.moderated_at) as moderated_at,max(torrents.slug) as slug,max(torrents.user_id) as user_id,max(torrents.name) as name,max(torrents.category_id) as category_id,max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed,max(torrents.created_at) as created_at,max(torrents.status) as status,count(distinct thanks.id) as thanked_total,max(bt.tipped_total) as tipped_total')
                ->withAnyStatus()->where('torrents.user_id', '=', $user->id)->leftJoin(DB::raw('(select distinct(bon_transactions.torrent_id),sum(bon_transactions.cost) as tipped_total from bon_transactions group by bon_transactions.torrent_id) as bt'), 'bt.torrent_id', '=', 'torrents.id')->leftJoin('thanks', 'thanks.torrent_id', 'torrents.id')->groupBy('torrents.id')->orderBy('created_at', 'DESC')->paginate(50);

            return view($logger, [
                'route'         => 'uploads',
                'user'          => $user,
                'uploads'       => $uploads,
                'his_upl'       => $his_upl,
                'his_upl_cre'   => $his_upl_cre,
                'his_downl'     => $his_downl,
                'his_downl_cre' => $his_downl_cre,
            ]);
        }
        $logger = 'user.uploads';
        $uploads = Torrent::selectRaw('distinct(torrents.id),max(torrents.moderated_at) as moderated_at,max(torrents.slug) as slug,max(torrents.user_id) as user_id,max(torrents.name) as name,max(torrents.category_id) as category_id,max(torrents.size) as size,max(torrents.leechers) as leechers,max(torrents.seeders) as seeders,max(torrents.times_completed) as times_completed,max(torrents.created_at) as created_at,max(torrents.status) as status,count(distinct thanks.id) as thanked_total,sum(bon_transactions.cost) as tipped_total')->where('torrents.user_id', '=', $user->id)->where('torrents.status', '=', 1)->where('torrents.anon', '=', 0)->with(['tips', 'thanks'])->leftJoin('bon_transactions', 'bon_transactions.torrent_id', 'torrents.id')->leftJoin('thanks', 'thanks.torrent_id', 'torrents.id')->groupBy('torrents.id')->orderBy('created_at', 'DESC')->paginate(50);

        return view($logger, [
            'route'       => 'uploads',
            'user'        => $user,
            'uploads'     => $uploads,
        ]);
    }

    /**
     * Get A Users Active Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function active(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);

        $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
        $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
        $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
        $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');

        $active = Peer::with(['torrent' => function ($query) {
            $query->withAnyStatus();
        }])->sortable(['created_at' => 'desc'])
            ->where('user_id', '=', $user->id)
            ->distinct('info_hash')
            ->paginate(50);

        return view('user.private.active', ['user' => $user,
            'route'                                => 'active',
            'active'                               => $active,
            'his_upl'                              => $his_upl,
            'his_upl_cre'                          => $his_upl_cre,
            'his_downl'                            => $his_downl,
            'his_downl_cre'                        => $his_downl_cre,
        ]);
    }

    /**
     * Get A Users Seeds Table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function seeds(Request $request, $username)
    {
        $user = User::where('username', '=', $username)->firstOrFail();

        abort_unless($request->user()->group->is_modo || $request->user()->id == $user->id, 403);

        $his_upl = History::where('user_id', '=', $user->id)->sum('actual_uploaded');
        $his_upl_cre = History::where('user_id', '=', $user->id)->sum('uploaded');
        $his_downl = History::where('user_id', '=', $user->id)->sum('actual_downloaded');
        $his_downl_cre = History::where('user_id', '=', $user->id)->sum('downloaded');

        $seeds = Peer::with(['torrent' => function ($query) {
            $query->withAnyStatus();
        }])->selectRaw('distinct(torrents.info_hash),max(peers.id) as id,max(torrents.name) as name,max(torrents.seeders) as seeders,max(torrents.leechers) as leechers,max(torrents.times_completed) as times_completed,max(torrents.size) as size,max(history.info_hash) as history_info_hash,max(history.created_at) as history_created_at,max(torrents.id) as torrent_id,max(history.seedtime) as seedtime')->leftJoin('torrents', 'torrents.id', '=', 'peers.torrent_id')->leftJoin('history', 'history.info_hash', '=', 'torrents.info_hash')->where('peers.user_id', '=', $user->id)->whereRaw('history.user_id = ? and history.seeder = ?', [$user->id, 1])
            ->where('peers.seeder', '=', 1)->orderBy('history_created_at', 'DESC')->groupBy('torrents.info_hash')
            ->paginate(50);

        return view('user.private.seeds', ['user' => $user,
            'route'                               => 'seeds',
            'seeds'                               => $seeds,
            'his_upl'                             => $his_upl,
            'his_upl_cre'                         => $his_upl_cre,
            'his_downl'                           => $his_downl,
            'his_downl_cre'                       => $his_downl_cre,
        ]);
    }

    /**
     * Get A Users Bans.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getBans(Request $request, $username)
    {
        abort_unless($request->user()->group->is_modo, 403);

        $user = User::where('username', '=', $username)->firstOrFail();
        $bans = Ban::where('owned_by', '=', $user->id)->latest()->get();

        return view('user.banlog', [
            'user'      => $user,
            'bans'      => $bans,
        ]);
    }

    /**
     * Download All History Torrents.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $username
     *
     * @return \ZipArchive
     */
    public function downloadHistoryTorrents(Request $request, $username)
    {
        //  Extend The Maximum Execution Time
        set_time_limit(300);

        // Authorized User
        $user = User::where('username', '=', $username)->firstOrFail();
        abort_unless($request->user()->id == $user->id, 403);

        // Define Dir Folder
        $path = getcwd().'/files/tmp_zip/';

        // Check Directory exists
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        // Zip File Name
        $zipFileName = sprintf('%s.zip', $user->username);

        // Create ZipArchive Obj
        $zip = new ZipArchive();

        // Get Users History
        $historyTorrents = History::where('user_id', '=', $user->id)->pluck('info_hash');

        if ($zip->open($path.'/'.$zipFileName, ZipArchive::CREATE) === true) {
            // Match History Results To Torrents
            $failCSV = '"Name","URL","ID","info_hash"
';
            $failCount = 0;
            foreach ($historyTorrents as $historyTorrent) {
                // Get Torrent
                $torrent = Torrent::withAnyStatus()->where('info_hash', '=', $historyTorrent)->first();

                // Define The Torrent Filename
                $tmpFileName = sprintf('%s.torrent', $torrent->slug);

                // The Torrent File Exist?
                if (! file_exists(getcwd().'/files/torrents/'.$torrent->file_name)) {
                    $failCSV .= '"'.$torrent->name.'","'.route('torrent', ['id' => $torrent->id]).'","'.$torrent->id.'","'.$historyTorrent.'"
';
                    $failCount++;
                } else {
                    // Delete The Last Torrent Tmp File If Exist
                    if (file_exists(getcwd().'/files/tmp/'.$tmpFileName)) {
                        unlink(getcwd().'/files/tmp/'.$tmpFileName);
                    }

                    // Get The Content Of The Torrent
                    $dict = Bencode::bdecode(file_get_contents(getcwd().'/files/torrents/'.$torrent->file_name));
                    // Set the announce key and add the user passkey
                    $dict['announce'] = route('announce', ['passkey' => $user->passkey]);
                    // Remove Other announce url
                    unset($dict['announce-list']);

                    $fileToDownload = Bencode::bencode($dict);
                    file_put_contents(getcwd().'/files/tmp/'.$tmpFileName, $fileToDownload);

                    // Add Files To ZipArchive
                    $zip->addFile(getcwd().'/files/tmp/'.$tmpFileName, $tmpFileName);
                }
            }
            if ($failCount > 0) {
                $CSVtmpName = sprintf('%s.zip', $user->username).'-missingTorrentFiles.CSV';
                file_put_contents(getcwd().'/files/tmp/'.$CSVtmpName, $failCSV);
                $zip->addFile(getcwd().'/files/tmp/'.$CSVtmpName, 'missingTorrentFiles.CSV');
            }
            // Close ZipArchive
            $zip->close();
        }

        $zip_file = $path.'/'.$zipFileName;

        if (file_exists($zip_file)) {
            return response()->download($zip_file)->deleteFileAfterSend(true);
        }

        return redirect()->back()->withErrors('Something Went Wrong!');
    }

    /**
     * Accept Site Rules.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function acceptRules(Request $request)
    {
        $user = $request->user();
        $user->read_rules = 1;
        $user->save();
    }
}
