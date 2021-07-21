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
use App\Http\Requests\StorePoll;
use App\Models\Option;
use App\Models\Poll;
use App\Repositories\ChatRepository;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\Staff\PollControllerTest
 */
class PollController extends Controller
{
    /**
     * PollController Constructor.
     */
    public function __construct(private ChatRepository $chatRepository)
    {
    }

    /**
     * Display All Polls.
     */
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $polls = Poll::latest()->paginate(25);

        return \view('Staff.poll.index', ['polls' => $polls]);
    }

    /**
     * Show A Poll.
     */
    public function show(Poll $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $poll = Poll::where('id', '=', $id)->firstOrFail();

        return \view('Staff.poll.show', ['poll' => $poll]);
    }

    /**
     * Poll Add Form.
     */
    public function create(): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        return \view('Staff.poll.create');
    }

    /**
     * Store A New Poll.
     */
    public function store(StorePoll $storePoll): \Illuminate\Http\RedirectResponse
    {
        $user = $storePoll->user();

        $poll = $storePoll->user() ? $user->polls()->create($storePoll->all()) : Poll::create($storePoll->all());

        $options = \collect($storePoll->input('options'))->map(fn ($value) => new Option(['name' => $value]));
        $poll->options()->saveMany($options);

        $pollUrl = \href_poll($poll);

        $this->chatRepository->systemMessage(
            \sprintf('A new poll has been created [url=%s]%s[/url] vote on it now! :slight_smile:', $pollUrl, $poll->title)
        );

        return \redirect()->route('staff.polls.index')
            ->withSuccess('Your poll has been created.');
    }

    /**
     * Poll Edit Form.
     */
    public function edit(Poll $id): \Illuminate\Contracts\View\Factory | \Illuminate\View\View
    {
        $poll = Poll::findOrFail($id);

        return \view('Staff.poll.edit', ['poll' => $poll]);
    }

    /**
     * Update A New Poll.
     *
     * @param \App\Http\Requests\StorePoll $storePoll
     * @param                              $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(StorePoll $storePoll, $id): \Illuminate\Http\RedirectResponse
    {
        $poll = Poll::findOrFail($id);

        $poll->title = $storePoll->input('title');

        $poll->multiple_choice = (bool) $storePoll->input('multiple_choice');

        // Remove the deleted options in poll
        $oldOptionIds = $poll->options->map(fn ($option) => $option->id)->all();

        $existingOldOptionIds = \collect($storePoll->input('option-id'))->map(fn ($id) => (int) $id)->all();

        foreach (\array_diff($oldOptionIds, $existingOldOptionIds) as $id) {
            $option = Option::findOrFail($id);
            $option->delete();
        }

        // Update existing options
        $existingOldOptionContents = \collect($storePoll->input('option-content'))->map(fn ($content) => (string) $content)->all();

        if (\count($existingOldOptionContents) === \count($existingOldOptionIds)) {
            $len = \count($existingOldOptionContents);
            for ($i = 0; $i < $len; $i++) {
                $option = Option::findOrFail($existingOldOptionIds[$i]);
                $option->name = $existingOldOptionContents[$i];
                $option->save();
            }
        }

        // Insert new options
        $newOptions = \collect($storePoll->input('new-option-content'))->map(fn ($content) => new Option(['name' => $content]));

        $poll->options()->saveMany($newOptions);

        // Last work from store()
        $pollUrl = \href_poll($poll);

        $this->chatRepository->systemMessage(
            \sprintf('A poll has been updated [url=%s]%s[/url] vote on it now! :slight_smile:', $pollUrl, $poll->title)
        );

        $poll->save();

        return \redirect()->route('staff.polls.index')
            ->withSuccess('Your poll has been edited.');
    }

    /**
     * Delete A Poll.
     *
     * @param \App\Models\Poll $id
     *
     *@throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Poll $id): \Illuminate\Http\RedirectResponse
    {
        $poll = Poll::findOrFail($id);
        $poll->delete();

        return \redirect()->route('staff.polls.index')
            ->withSuccess('Poll has successfully been deleted');
    }
}
