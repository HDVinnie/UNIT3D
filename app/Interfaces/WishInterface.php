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

namespace App\Interfaces;

interface WishInterface
{
    public function all();

    public function findById($id);

    public function findByTitle($title);

    public function exists($uid, $id);

    public function isGranted($id);

    public function getSource($id);

    public function getUserWishes($uid);

    public function create(array $data);

    public function delete($id);
}
