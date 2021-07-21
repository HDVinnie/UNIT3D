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

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\PersonalFreeleech;
use App\Models\Torrent;
use Illuminate\Http\Request;

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
        $categories = Category::withCount('torrents')->get()->sortBy('position');

        return \view('category.index', ['categories' => $categories]);
    }

    /**
     * Show A Category.
     */
    public function show(Request $request, Category $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $user = $request->user();
        $category = Category::select(['id', 'name'])->findOrFail($id);
        $torrents = Torrent::with(['user', 'category', 'type'])->withCount(['thanks', 'comments'])->where('category_id', '=', $id)->orderBy('sticky', 'desc')->latest()->paginate(25);
        $personalFreeleech = PersonalFreeleech::where('user_id', '=', $user->id)->first();
        $bookmarks = Bookmark::where('user_id', $user->id)->get();

        return \view('category.show', [
            'torrents'           => $torrents,
            'user'               => $user,
            'category'           => $category,
            'personal_freeleech' => $personalFreeleech,
            'bookmarks'          => $bookmarks,
        ]);
    }
}
