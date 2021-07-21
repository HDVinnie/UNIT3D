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
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\TypeControllerTest
 */
class TypeController extends Controller
{
    /**
     * Display All Types.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $types = Type::all()->sortBy('position');

        return \view('Staff.type.index', ['types' => $types]);
    }

    /**
     * Show Type Create Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.type.create');
    }

    /**
     * Store A New Type.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $type = new Type();
        $type->name = $request->input('name');
        $type->slug = Str::slug($type->name);
        $type->position = $request->input('position');

        $v = \validator($type->toArray(), [
            'name'     => 'required',
            'slug'     => 'required',
            'position' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.types.index')
                ->withErrors($v->errors());
        }
        $type->save();

        return \redirect()->route('staff.types.index')
            ->withSuccess('Type Successfully Added');
    }

    /**
     * Type Edit Form.
     */
    public function edit(Type $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $type = Type::findOrFail($id);

        return \view('Staff.type.edit', ['type' => $type]);
    }

    /**
     * Edit A Type.
     */
    public function update(Request $request, Type $id): \Illuminate\Http\RedirectResponse
    {
        $type = Type::findOrFail($id);
        $type->name = $request->input('name');
        $type->slug = Str::slug($type->name);
        $type->position = $request->input('position');

        $v = \validator($type->toArray(), [
            'name'     => 'required',
            'slug'     => 'required',
            'position' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.types.index')
                ->withErrors($v->errors());
        }
        $type->save();

        return \redirect()->route('staff.types.index')
            ->withSuccess('Type Successfully Modified');
    }

    /**
     * Delete A Type.
     *
     * @param \App\Models\Type $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Type $id): \Illuminate\Http\RedirectResponse
    {
        $type = Type::findOrFail($id);
        $type->delete();

        return \redirect()->route('staff.types.index')
            ->withSuccess('Type Successfully Deleted');
    }
}
