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

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\NotificationControllerTest
 */
class NotificationController extends Controller
{
    /**
     * Show All Notifications.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $notifications = $request->user()->notifications()->paginate(25);

        return \view('notification.index', ['notifications' => $notifications]);
    }

    /**
     * Uses Input's To Put Together A Search.
     *
     *
     *@throws \Throwable
     */
    public function faceted(Request $request): string
    {
        $user = $request->user();

        $notification = $user->notifications();

        if ($request->has('bon_gifts') && $request->input('bon_gifts') != null) {
            $notification->where('type', '=', \App\Notifications\NewBon::class);
        }

        if ($request->has('comments') && $request->input('comments') != null) {
            $notification->where('type', '=', \App\Notifications\NewComment::class);
        }

        if ($request->has('comment_tags') && $request->input('comment_tags') != null) {
            $notification->where('type', '=', \App\Notifications\NewCommentTag::class);
        }

        if ($request->has('followers') && $request->input('followers') != null) {
            $notification->where('type', '=', \App\Notifications\NewFollow::class);
        }

        if ($request->has('posts') && $request->input('posts') != null) {
            $notification->where('type', '=', \App\Notifications\NewPost::class);
        }

        if ($request->has('post_tags') && $request->input('post_tags') != null) {
            $notification->where('type', '=', \App\Notifications\NewPostTag::class);
        }

        if ($request->has('post_tips') && $request->input('post_tips') != null) {
            $notification->where('type', '=', \App\Notifications\NewPostTip::class);
        }

        if ($request->has('request_bounties') && $request->input('request_bounties') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestBounty::class);
        }

        if ($request->has('request_claims') && $request->input('request_claims') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestClaim::class);
        }

        if ($request->has('request_fills') && $request->input('request_fills') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestFill::class);
        }

        if ($request->has('request_approvals') && $request->input('request_approvals') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestFillApprove::class);
        }

        if ($request->has('request_rejections') && $request->input('request_rejections') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestFillReject::class);
        }

        if ($request->has('request_unclaims') && $request->input('request_unclaims') != null) {
            $notification->where('type', '=', \App\Notifications\NewRequestUnclaim::class);
        }

        if ($request->has('reseed_requests') && $request->input('reseed_requests') != null) {
            $notification->where('type', '=', \App\Notifications\NewReseedRequest::class);
        }

        if ($request->has('thanks') && $request->input('thanks') != null) {
            $notification->where('type', '=', \App\Notifications\NewThank::class);
        }

        if ($request->has('upload_tips') && $request->input('upload_tips') != null) {
            $notification->where('type', '=', \App\Notifications\NewUploadTip::class);
        }

        if ($request->has('topics') && $request->input('topics') != null) {
            $notification->where('type', '=', \App\Notifications\NewTopic::class);
        }

        if ($request->has('unfollows') && $request->input('unfollows') != null) {
            $notification->where('type', '=', \App\Notifications\NewUnfollow::class);
        }

        if ($request->has('uploads') && $request->input('uploads') != null) {
            $notification->where('type', '=', \App\Notifications\NewUpload::class);
        }

        $notifications = $notification->paginate(25);

        return \view('notification.results', [
            'user'            => $user,
            'notifications'   => $notifications,
        ])->render();
    }

    /**
     * Show A Notification And Mark As Read.
     */
    public function show(Request $request, \App\Models\Notification $id): \Illuminate\Http\RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return \redirect()->to($notification->data['url'])
            ->withSuccess('Notification Marked As Read!');
    }

    /**
     * Set A Notification To Read.
     */
    public function update(Request $request, \App\Models\Notification $id): \Illuminate\Http\RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', '=', $id)->first();

        if (! $notification) {
            return \redirect()->route('notifications.index')
                ->withErrors('Notification Does Not Exist!');
        }

        if ($notification->read_at != null) {
            return \redirect()->route('notifications.index')
                ->withErrors('Notification Already Marked As Read!');
        }

        $notification->markAsRead();

        return \redirect()->route('notifications.index')
            ->withSuccess('Notification Marked As Read!');
    }

    /**
     * Mass Update All Notification's To Read.
     *
     *
     * @throws \Exception
     */
    public function updateAll(Request $request): \Illuminate\Http\RedirectResponse
    {
        $carbon = new Carbon();
        $request->user()->unreadNotifications()->update(['read_at' => $carbon]);

        return \redirect()->route('notifications.index')
            ->withSuccess('All Notifications Marked As Read!');
    }

    /**
     * Delete A Notification.
     */
    public function destroy(Request $request, \App\Models\Notification $id): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return \redirect()->route('notifications.index')
            ->withSuccess('Notification Deleted!');
    }

    /**
     * Mass Delete All Notification's.
     */
    public function destroyAll(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->delete();

        return \redirect()->route('notifications.index')
            ->withSuccess('All Notifications Deleted!');
    }
}
