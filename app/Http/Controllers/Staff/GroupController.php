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
use App\Models\Forum;
use App\Models\Group;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\GroupControllerTest
 */
class GroupController extends Controller
{
    /**
     * Display All Groups.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        \abort_unless($user->group->is_admin, 403);

        $groups = Group::all()->sortBy('position');

        return \view('Staff.group.index', ['groups' => $groups]);
    }

    /**
     * Group Add Form.
     */
    public function create(Request $request): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        \abort_unless($user->group->is_admin, 403);

        return \view('Staff.group.create');
    }

    /**
     * Store A New Group.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_admin, 403);

        $group = new Group();
        $group->name = $request->input('name');
        $group->slug = Str::slug($request->input('name'));
        $group->position = $request->input('position');
        $group->level = $request->input('level');
        $group->color = $request->input('color');
        $group->icon = $request->input('icon');
        $group->effect = $request->input('effect');
        $group->is_internal = $request->input('is_internal');
        $group->is_modo = $request->input('is_modo');
        $group->is_admin = $request->input('is_admin');
        $group->is_owner = $request->input('is_owner');
        $group->is_trusted = $request->input('is_trusted');
        $group->is_immune = $request->input('is_immune');
        $group->is_freeleech = $request->input('is_freeleech');
        $group->is_double_upload = $request->input('is_double_upload');
        $group->is_incognito = $request->input('is_incognito');
        $group->can_upload = $request->input('can_upload');
        $group->autogroup = $request->input('autogroup');

        $v = \validator($group->toArray(), [
            'name'     => 'required|unique:groups',
            'slug'     => 'required|unique:groups',
            'position' => 'required',
            'color'    => 'required',
            'icon'     => 'required',
        ]);

        if (! $request->user()->group->is_owner && $request->input('is_owner') == 1) {
            return \redirect()->route('staff.groups.index')
                ->withErrors('You are not permitted to create a group with owner permissions!');
        }

        if ($v->fails()) {
            return \redirect()->route('staff.groups.index')
                ->withErrors($v->errors());
        }
        $group->save();
        foreach (Forum::all()->pluck('id') as $collection) {
            $permission = new Permission();
            $permission->forum_id = $collection;
            $permission->group_id = $group->id;
            $permission->show_forum = 1;
            $permission->read_topic = 1;
            $permission->reply_topic = 1;
            $permission->start_topic = 1;
            $permission->save();
        }

        return \redirect()->route('staff.groups.index')
            ->withSuccess('Group Was Created Successfully!');
    }

    /**
     * Group Edit Form.
     */
    public function edit(Request $request, Group $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        \abort_unless($user->group->is_admin, 403);

        $group = Group::findOrFail($id);

        return \view('Staff.group.edit', ['group' => $group]);
    }

    /**
     * Edit A Group.
     */
    public function update(Request $request, Group $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        \abort_unless($user->group->is_admin, 403);

        $group = Group::findOrFail($id);

        $group->name = $request->input('name');
        $group->slug = Str::slug($request->input('name'));
        $group->position = $request->input('position');
        $group->level = $request->input('level');
        $group->color = $request->input('color');
        $group->icon = $request->input('icon');
        $group->effect = $request->input('effect');
        $group->is_internal = $request->input('is_internal');
        $group->is_modo = $request->input('is_modo');
        $group->is_admin = $request->input('is_admin');
        $group->is_owner = $request->input('is_owner');
        $group->is_trusted = $request->input('is_trusted');
        $group->is_immune = $request->input('is_immune');
        $group->is_freeleech = $request->input('is_freeleech');
        $group->is_double_upload = $request->input('is_double_upload');
        $group->is_incognito = $request->input('is_incognito');
        $group->can_upload = $request->input('can_upload');
        $group->autogroup = $request->input('autogroup');

        $v = \validator($group->toArray(), [
            'name'     => 'required',
            'slug'     => 'required',
            'position' => 'required',
            'color'    => 'required',
            'icon'     => 'required',
        ]);

        if (! $request->user()->group->is_owner && $request->input('is_owner') == 1) {
            return \redirect()->route('staff.groups.index')
                ->withErrors('You are not permitted to give a group owner permissions!');
        }

        if ($v->fails()) {
            return \redirect()->route('staff.groups.index')
                ->withErrors($v->errors());
        }
        $group->save();

        return \redirect()->route('staff.groups.index')
            ->withSuccess('Group Was Updated Successfully!');
    }
}
