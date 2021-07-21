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

namespace App\Helpers;

use ZipArchive;

class BackupEncryption
{
    /**
     * Default encryption contants.
     *
     * @var string
     */
    public const ENCRYPTION_DEFAULT = ZipArchive::EM_AES_128;

    /**
     * AES-128 encryption contants.
     *
     * @var string
     */
    public const ENCRYPTION_WINZIP_AES_128 = ZipArchive::EM_AES_128;

    /**
     * AES-192 encryption contants.
     *
     * @var string
     */
    public const ENCRYPTION_WINZIP_AES_192 = ZipArchive::EM_AES_192;

    /**
     * AES-256 encryption contants.
     *
     * @var string
     */
    public const ENCRYPTION_WINZIP_AES_256 = ZipArchive::EM_AES_256;
}
