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
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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
        $articles = Article::latest()->paginate(25);

        return \view('Staff.article.index', ['articles' => $articles]);
    }

    /**
     * Article Add Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.article.create');
    }

    /**
     * Store A New Article.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $article = new Article();
        $article->title = $request->input('title');
        $article->slug = Str::slug($article->title);
        $article->content = $request->input('content');
        $article->user_id = $request->user()->id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'article-'.\uniqid('', true).'.'.$image->getClientOriginalExtension();
            $path = \public_path('/files/img/'.$filename);
            Image::make($image->getRealPath())->fit(75, 75)->encode('png', 100)->save($path);
            $article->image = $filename;
        } else {
            // Use Default /public/img/missing-image.png
            $article->image = null;
        }

        $v = \validator($article->toArray(), [
            'title'   => 'required',
            'slug'    => 'required',
            'content' => 'required|min:20',
            'user_id' => 'required',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.articles.index')
                ->withErrors($v->errors());
        }
        $article->save();

        return \redirect()->route('staff.articles.index')
            ->withSuccess('Your article has successfully published!');
    }

    /**
     * Article Edit Form.
     */
    public function edit(Article $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $article = Article::findOrFail($id);

        return \view('Staff.article.edit', ['article' => $article]);
    }

    /**
     * Edit A Article.
     */
    public function update(Request $request, Article $id): \Illuminate\Http\RedirectResponse
    {
        $article = Article::findOrFail($id);
        $article->title = $request->input('title');
        $article->slug = Str::slug($article->title);
        $article->content = $request->input('content');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'article-'.\uniqid('', true).'.'.$image->getClientOriginalExtension();
            $path = \public_path('/files/img/'.$filename);
            Image::make($image->getRealPath())->fit(75, 75)->encode('png', 100)->save($path);
            $article->image = $filename;
        } else {
            // Use Default /public/img/missing-image.png
            $article->image = null;
        }

        $v = \validator($article->toArray(), [
            'title'   => 'required',
            'slug'    => 'required',
            'content' => 'required|min:20',
        ]);

        if ($v->fails()) {
            return \redirect()->route('staff.articles.index')
                ->withErrors($v->errors());
        }
        $article->save();

        return \redirect()->route('staff.articles.index')
            ->withSuccess('Your article changes have successfully published!');
    }

    /**
     * Delete A Article.
     *
     * @param \App\Models\Article $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Article $id): \Illuminate\Http\RedirectResponse
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return \redirect()->route('staff.articles.index')
            ->withSuccess('Article has successfully been deleted');
    }
}
