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

use App\Models\Article;

/**
 * @see \Tests\Feature\Http\Controllers\ArticleControllerTest
 */
class ArticleController extends Controller
{
    /**
     * Display All Articles.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $articles = Article::latest()->paginate(6);

        return \view('article.index', ['articles' => $articles]);
    }

    /**
     * Show A Article.
     */
    public function show(Article $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $article = Article::with(['user', 'comments'])->findOrFail($id);

        return \view('article.show', ['article' => $article]);
    }
}
