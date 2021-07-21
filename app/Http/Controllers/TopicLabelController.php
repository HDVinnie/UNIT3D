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

use App\Models\Topic;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\TopicLabelControllerTest
 */
class TopicLabelController extends Controller
{
    /**
     * Apply/Remove Approved Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->approved = $topic->approved == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Denied Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deny(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->denied = $topic->denied == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Solved Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function solve(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->solved = $topic->solved == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Invalid Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invalid(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->invalid = $topic->invalid == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Bug Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bug(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->bug = $topic->bug == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Suggestion Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suggest(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->suggestion = $topic->suggestion == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }

    /**
     * Apply/Remove Implemented Label.
     *
     * @param \App\Models\Topic $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function implement(Topic $id): \Illuminate\Http\RedirectResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->implemented = $topic->implemented == 0 ? '1' : '0';
        $topic->save();

        return \redirect()->route('forum_topic', ['id' => $topic->id])
            ->withInfo('Label Change Has Been Applied');
    }
}
