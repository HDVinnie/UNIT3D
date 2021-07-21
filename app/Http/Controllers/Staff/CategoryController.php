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
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * @see \Tests\Feature\Http\Controllers\CategoryControllerTest
 */
class CategoryController extends Controller
{
    /**
     * Display All Categories.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $categories = Category::all()->sortBy('position');

        return \view('Staff.category.index', ['categories' => $categories]);
    }

    /**
     * Show Form For Creating A New Category.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.category.create');
    }

    /**
     * Store A Category.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $category = new Category();
        $category->name = $request->input('name');
        $category->slug = Str::slug($category->name);
        $category->position = $request->input('position');
        $category->icon = $request->input('icon');
        $category->movie_meta = $request->input('movie_meta');
        $category->tv_meta = $request->input('tv_meta');
        $category->game_meta = $request->input('game_meta');
        $category->music_meta = $request->input('music_meta');
        $category->no_meta = $request->input('no_meta');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'category-'.\uniqid('', true).'.'.$image->getClientOriginalExtension();
            $path = \public_path('/files/img/'.$filename);
            Image::make($image->getRealPath())->fit(50, 50)->encode('png', 100)->save($path);
            $category->image = $filename;
        } else {
            $category->image = null;
        }

        $v = \validator($category->toArray(), [
            'name'          => 'required',
            'slug'          => 'required',
            'position'      => 'required',
            'icon'          => 'required',
            'movie_meta'    => 'required',
            'tv_meta'       => 'required',
            'game_meta'     => 'required',
            'music_meta'    => 'required',
            'no_meta'       => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.categories.index')
                ->withErrors($v->errors());
        }
        $category->save();

        return \redirect()->route('staff.categories.index')
            ->withSuccess('Category Successfully Added');
    }

    /**
     * Category Edit Form.
     */
    public function edit(Category $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $category = Category::findOrFail($id);

        return \view('Staff.category.edit', ['category' => $category]);
    }

    /**
     * Update A Category.
     */
    public function update(Request $request, Category $id): \Illuminate\Http\RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->name = $request->input('name');
        $category->slug = Str::slug($category->name);
        $category->position = $request->input('position');
        $category->icon = $request->input('icon');
        $category->movie_meta = $request->input('movie_meta');
        $category->tv_meta = $request->input('tv_meta');
        $category->game_meta = $request->input('game_meta');
        $category->music_meta = $request->input('music_meta');
        $category->no_meta = $request->input('no_meta');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'category-'.\uniqid('', true).'.'.$image->getClientOriginalExtension();
            $path = \public_path('/files/img/'.$filename);
            Image::make($image->getRealPath())->fit(50, 50)->encode('png', 100)->save($path);
            $category->image = $filename;
        }

        $v = \validator($category->toArray(), [
            'name'          => 'required',
            'slug'          => 'required',
            'position'      => 'required',
            'icon'          => 'required',
            'movie_meta'    => 'required',
            'tv_meta'       => 'required',
            'game_meta'     => 'required',
            'music_meta'    => 'required',
            'no_meta'       => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.categories.index')
                ->withErrors($v->errors());
        }
        $category->save();

        return \redirect()->route('staff.categories.index')
            ->withSuccess('Category Successfully Modified');
    }

    /**
     * Destroy A Category.
     *
     * @param \App\Models\Category $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $id): \Illuminate\Http\RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return \redirect()->route('staff.categories.index')
            ->withSuccess('Category Successfully Deleted');
    }
}
