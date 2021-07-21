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

use App\Achievements\UserUploaded1000Subtitles;
use App\Achievements\UserUploaded100Subtitles;
use App\Achievements\UserUploaded200Subtitles;
use App\Achievements\UserUploaded25Subtitles;
use App\Achievements\UserUploaded300Subtitles;
use App\Achievements\UserUploaded400Subtitles;
use App\Achievements\UserUploaded500Subtitles;
use App\Achievements\UserUploaded50Subtitles;
use App\Achievements\UserUploaded600Subtitles;
use App\Achievements\UserUploaded700Subtitles;
use App\Achievements\UserUploaded800Subtitles;
use App\Achievements\UserUploaded900Subtitles;
use App\Achievements\UserUploadedFirstSubtitle;
use App\Models\MediaLanguage;
use App\Models\Subtitle;
use App\Models\Torrent;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubtitleController extends Controller
{
    /**
     * SubtitleController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('subtitle.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Torrent $torrentId): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $torrent = Torrent::findOrFail($torrentId);
        $mediaLanguages = MediaLanguage::all()->sortBy('name');

        return \view('subtitle.create', ['torrent' => $torrent, 'media_languages' => $mediaLanguages]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $subtitleFile = $request->file('subtitle_file');
        $filename = \uniqid('', true).'.'.$subtitleFile->getClientOriginalExtension();

        $subtitle = new Subtitle();
        $subtitle->title = $request->input('torrent_name');
        $subtitle->file_name = $filename;
        $subtitle->file_size = $subtitleFile->getSize();
        $subtitle->extension = '.'.$subtitleFile->getClientOriginalExtension();
        $subtitle->language_id = $request->input('language_id');
        $subtitle->note = $request->input('note');
        $subtitle->downloads = 0;
        $subtitle->verified = 0;
        $subtitle->user_id = $user->id;
        $subtitle->anon = $request->input('anonymous');
        $subtitle->torrent_id = $request->input('torrent_id');
        $subtitle->status = 1;
        $subtitle->moderated_at = \now();
        $subtitle->moderated_by = 1;

        $v = \validator($subtitle->toArray(), [
            'title'       => 'required',
            'file_name'   => 'required',
            'file_size'   => 'required',
            'extension'   => 'required|in:.srt,.ass,.sup,.zip',
            'language_id' => 'required',
            'user_id'     => 'required',
            'torrent_id'  => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('subtitles.create', ['torrent_id' => $request->input('torrent_id')])
                ->withErrors($v->errors());
        }

        // Save Subtitle
        Storage::disk('subtitles')->put($filename, \file_get_contents($subtitleFile));
        $subtitle->save();

        // Announce To Shoutbox
        $torrentUrl = \href_torrent($subtitle->torrent);
        $profileUrl = \href_profile($user);
        if ($subtitle->anon == false) {
            $this->chatRepository->systemMessage(
                \sprintf('[url=%s]%s[/url] has uploaded a new %s subtitle for [url=%s]%s[/url]', $profileUrl, $user->username, $subtitle->language->name, $torrentUrl, $subtitle->torrent->name)
            );
        } else {
            $this->chatRepository->systemMessage(
                \sprintf('An anonymous user has uploaded a new %s subtitle for [url=%s]%s[/url]', $subtitle->language->name, $torrentUrl, $subtitle->torrent->name)
            );
        }

        // Achievements
        $user->unlock(new UserUploadedFirstSubtitle(), 1);
        $user->addProgress(new UserUploaded25Subtitles(), 1);
        $user->addProgress(new UserUploaded50Subtitles(), 1);
        $user->addProgress(new UserUploaded100Subtitles(), 1);
        $user->addProgress(new UserUploaded200Subtitles(), 1);
        $user->addProgress(new UserUploaded300Subtitles(), 1);
        $user->addProgress(new UserUploaded400Subtitles(), 1);
        $user->addProgress(new UserUploaded500Subtitles(), 1);
        $user->addProgress(new UserUploaded600Subtitles(), 1);
        $user->addProgress(new UserUploaded700Subtitles(), 1);
        $user->addProgress(new UserUploaded800Subtitles(), 1);
        $user->addProgress(new UserUploaded900Subtitles(), 1);
        $user->addProgress(new UserUploaded1000Subtitles(), 1);

        return \redirect()->route('torrent', ['id' => $request->input('torrent_id')])
            ->withSuccess('Subtitle Successfully Added');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subtitle $id): \Illuminate\Http\RedirectResponse
    {
        $subtitle = Subtitle::findOrFail($id);

        $user = $request->user();
        \abort_unless($user->group->is_modo || $user->id == $subtitle->user_id, 403);

        $subtitle->language_id = $request->input('language_id');
        $subtitle->note = $request->input('note');

        $v = \validator($subtitle->toArray(), [
            'language_id' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('torrent', ['id' => $request->input('torrent_id')])
                ->withErrors($v->errors());
        }
        $subtitle->save();

        return \redirect()->route('torrent', ['id' => $request->input('torrent_id')])
            ->withSuccess('Subtitle Successfully Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     *@throws \Exception
     */
    public function destroy(Request $request, Subtitle $id): \Illuminate\Http\RedirectResponse
    {
        $subtitle = Subtitle::findOrFail($id);

        $user = $request->user();
        \abort_unless($user->group->is_modo || $user->id == $subtitle->user_id, 403);

        if (Storage::disk('subtitles')->exists($subtitle->file_name)) {
            Storage::disk('subtitles')->delete($subtitle->file_name);
        }

        $subtitle->delete();

        return \redirect()->route('torrent', ['id' => $request->input('torrent_id')])
            ->withSuccess('Subtitle Successfully Deleted');
    }

    /**
     * Download the specified resource from storage.
     */
    public function download(Request $request, Subtitle $id): \Illuminate\Http\RedirectResponse | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $subtitle = Subtitle::findOrFail($id);
        $user = $request->user();

        // User's download rights are revoked
        if ($user->can_download == 0 && $subtitle->user_id != $user->id) {
            return \redirect()->route('torrent', ['id' => $subtitle->torrent->id])
                ->withErrors('Your Download Rights Have Been Revoked!');
        }

        // Define the filename for the download
        $tempFilename = '['.$subtitle->language->name.' Subtitle]'.$subtitle->torrent->name.$subtitle->extension;

        // Increment downloads count
        $subtitle->increment('downloads');

        $headers = ['Content-Type: '.Storage::disk('subtitles')->mimeType($subtitle->file_name)];

        return Storage::disk('subtitles')->download($subtitle->file_name, $tempFilename, $headers);
    }
}
