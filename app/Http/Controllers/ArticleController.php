<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use App\Models\Article;

final class ArticleController extends Controller
{
    /**
     * Display All Articles.
     *
     * @return Factory|View
     */
    public function index()
    {
        $articles = Article::latest()->paginate(6);

        return view('article.index', ['articles' => $articles]);
    }

    /**
     * Show A Article.
     *
     * @param $id
     *
     * @return Factory|View
     */
    public function show($id)
    {
        $article = Article::with(['user', 'comments'])->findOrFail($id);

        return view('article.show', ['article' => $article]);
    }
}
