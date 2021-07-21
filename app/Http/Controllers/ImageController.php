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

use App\Models\Album;
use App\Models\Image;
use Illuminate\Http\Request;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\ImageControllerTest
 */
class ImageController extends Controller
{
    /**
     * Show Image Create Form.
     */
    public function create(Album $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $album = Album::find($id);

        return \view('album.image', ['album' => $album]);
    }

    /**
     * Store A New Image.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $image = new Image();
        $image->user_id = $request->user()->id;
        $image->album_id = $request->input('album_id');
        $image->description = $request->input('description');
        $image->type = $request->input('type');

        $file = $request->file('image');
        $randomName = \uniqid('', true);
        $destinationPath = \public_path('/files/img/');
        $clientOriginalExtension = $file->getClientOriginalExtension();
        $filename = 'album-image_'.$randomName.'.'.$clientOriginalExtension;
        $uploadSuccess = $request->file('image')->move($destinationPath, $filename);
        $image->image = $filename;

        $v = \validator($image->toArray(), [
            'album_id'    => 'required|numeric|exists:albums,id',
            'user_id'     => 'required',
            'description' => 'required',
            'type'        => 'required',
            'image'       => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('images.create', ['id' => $request->input('album_id')])
                ->withErrors($v->errors());
        }
        $image->save();

        return \redirect()->route('albums.show', ['id' => $request->input('album_id')])
            ->withSuccess('Your image has successfully published!');
    }

    /**
     * Download A Image.
     *
     * @param \App\Models\Image $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Image $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $image = Image::findOrFail($id);
        $filename = $image->image;

        if (! \file_exists(\getcwd().'/files/img/'.$filename)) {
            return \redirect()->route('show_album', ['id' => $image->album_id])
                ->withErrors('Image File Not Found! Please Report This To Staff!');
        }

        $image->downloads++;
        $image->save();

        return \response()->download(\getcwd().'/files/img/'.$filename);
    }

    /**
     * Delete A Image.
     *
     *
     *@throws \Exception
     */
    public function destroy(Request $request, Image $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $image = Image::findOrFail($id);

        \abort_unless($user->group->is_modo || $user->id === $image->user_id, 403);
        $image->delete();

        return \redirect()->route('albums.show', ['id' => $image->album_id])
            ->withSuccess('Image has successfully been deleted');
    }
}
