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

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Resolution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResolutionController extends Controller
{
    /**
     * Display All Resolutions.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $resolutions = Resolution::all()->sortBy('position');

        return \view('Staff.resolution.index', ['resolutions' => $resolutions]);
    }

    /**
     * Show Resolution Create Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.resolution.create');
    }

    /**
     * Store A New Resolution.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $resolution = new Resolution();
        $resolution->name = $request->input('name');
        $resolution->slug = Str::slug($resolution->name);
        $resolution->position = $request->input('position');

        $v = \validator($resolution->toArray(), [
            'name'     => 'required',
            'slug'     => 'required',
            'position' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.resolutions.index')
                ->withErrors($v->errors());
        }
        $resolution->save();

        return \redirect()->route('staff.resolutions.index')
                ->withSuccess('Resolution Successfully Added');
    }

    /**
     * Resolution Edit Form.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $resolution = Resolution::findOrFail($id);

        return \view('Staff.resolution.edit', ['resolution' => $resolution]);
    }

    /**
     * Edit A Resolution.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $resolution = Resolution::findOrFail($id);
        $resolution->name = $request->input('name');
        $resolution->slug = Str::slug($resolution->name);
        $resolution->position = $request->input('position');

        $v = \validator($resolution->toArray(), [
            'name'     => 'required',
            'slug'     => 'required',
            'position' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.resolutions.index')
                ->withErrors($v->errors());
        }
        $resolution->save();

        return \redirect()->route('staff.resolutions.index')
                ->withSuccess('Resolution Successfully Modified');
    }

    /**
     * Delete A Resolution.
     *
     * @param $id
     *
     * @throws \Exception
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function destroy($id): Illuminate\Http\RedirectResponse
    {
        $resolution = Resolution::findOrFail($id);
        $resolution->delete();

        return \redirect()->route('staff.resolutions.index')
            ->withSuccess('Resolution Successfully Deleted');
    }
}
