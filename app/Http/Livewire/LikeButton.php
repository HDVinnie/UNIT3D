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

namespace App\Http\Livewire;

use App\Models\Like;
use App\Models\Post;
use Livewire\Component;

class LikeButton extends Component
{
    public $post;
    public $user;

    final public function mount($post): void
    {
        $this->user = \auth()->user();
        $this->post = Post::findOrFail($post);
    }

    final public function store(): void
    {
        if ($this->user->id === $this->post->user_id) {
            $this->dispatchBrowserEvent('error', ['type' => 'error',  'message' => 'You Cannot Like Your Own Post!']);

            return;
        }

        $exist = Like::where('user_id', '=', $this->user->id)->where('post_id', '=', $this->post->id)->first();
        if ($exist) {
            $this->dispatchBrowserEvent('error', ['type' => 'error',  'message' => 'You Have Already Liked Or Disliked This Post!']);

            return;
        }

        $new = new Like();
        $new->user_id = $this->user->id;
        $new->post_id = $this->post->id;
        $new->like = 1;
        $new->save();

        $this->dispatchBrowserEvent('success', ['type' => 'success',  'message' => 'Your Like Was Successfully Applied!']);
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.like-button');
    }
}
