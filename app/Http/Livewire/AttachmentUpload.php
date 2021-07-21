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

use App\Models\TicketAttachment;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttachmentUpload extends Component
{
    use WithFileUploads;

    public $user;
    public $ticket;
    public $attachment;
    public $storedImage;

    final public function mount(int $id): void
    {
        $this->user = \auth()->user();
        $this->ticket = $id;
    }

    final public function upload(): void
    {
        $this->validate([
            'attachment' => 'image|max:1024', // 1MB Max
        ]);

        $fileName = \uniqid('', true).'.'.$this->attachment->getClientOriginalExtension();

        $this->attachment->storeAs('attachments', $fileName, 'attachments');

        $attachment = new TicketAttachment();
        $attachment->user_id = $this->user->id;
        $attachment->ticket_id = $this->ticket;
        $attachment->file_name = $fileName;
        $attachment->file_size = $this->attachment->getSize();
        $attachment->file_extension = $this->attachment->getMimeType();
        $attachment->save();

        $this->dispatchBrowserEvent('success', ['type' => 'success',  'message' => 'Ticket Attachment Uploaded Successfully!']);
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return \view('livewire.attachment-upload');
    }
}
