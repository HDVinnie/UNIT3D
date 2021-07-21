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
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\PageControllerTest
 */
class PageController extends Controller
{
    /**
     * Display All Pages.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $pages = Page::all();

        return \view('Staff.page.index', ['pages' => $pages]);
    }

    /**
     * Page Add Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.page.create');
    }

    /**
     * Store A New Page.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $page = new Page();
        $page->name = $request->input('name');
        $page->slug = Str::slug($page->name);
        $page->content = $request->input('content');

        $v = \validator($page->toArray(), [
            'name'    => 'required',
            'slug'    => 'required',
            'content' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.pages.index')
                ->withErrors($v->errors());
        }
        $page->save();

        return \redirect()->route('staff.pages.index')
            ->withSuccess('Page has been created successfully');
    }

    /**
     * Page Edit Form.
     */
    public function edit(Page $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $page = Page::findOrFail($id);

        return \view('Staff.page.edit', ['page' => $page]);
    }

    /**
     * Edit A Page.
     */
    public function update(Request $request, Page $id): \Illuminate\Http\RedirectResponse
    {
        $page = Page::findOrFail($id);
        $page->name = $request->input('name');
        $page->slug = Str::slug($page->name);
        $page->content = $request->input('content');

        $v = \validator($page->toArray(), [
            'name'    => 'required',
            'slug'    => 'required',
            'content' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.pages.index')
                ->withErrors($v->errors());
        }
        $page->save();

        return \redirect()->route('staff.pages.index')
            ->withSuccess('Page has been edited successfully');
    }

    /**
     * Delete A Page.
     *
     * @param \App\Models\Page $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Page $id): \Illuminate\Http\RedirectResponse
    {
        Page::findOrFail($id)->delete();

        return \redirect()->route('staff.pages.index')
            ->withSuccess('Page has been deleted successfully');
    }
}
