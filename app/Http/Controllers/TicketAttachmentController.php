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

use App\Models\TicketAttachment;

class TicketAttachmentController extends Controller
{
    /**
     * Download a ticket attachment from storage.
     */
    final public function download(TicketAttachment $attachment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return \response()->download(\getcwd().'/files/attachments/attachments/'.$attachment->file_name)->deleteFileAfterSend(false);
    }
}
