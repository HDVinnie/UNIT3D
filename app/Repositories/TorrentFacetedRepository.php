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

namespace App\Repositories;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Resolution;
use App\Models\Type;

class TorrentFacetedRepository
{
    /**
     * Return a collection of Category Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */
    public function categories(): \Illuminate\Support\Collection
    {
        return Category::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Return a collection of Type Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */
    public function types(): \Illuminate\Support\Collection
    {
        return Type::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Return a collection of Resolution Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolutions(): \Illuminate\Support\Collection
    {
        return Resolution::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Return a collection of Tag Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */
    public function genres(): \Illuminate\Support\Collection
    {
        return Genre::all()->sortBy('name')->pluck('name', 'id');
    }

    /**
     * Options for sort the search result.
     *
     * @return array
     */
    public function sorting(): array
    {
        return [
            'bumped_at'       => \trans('torrent.date'),
            'name'            => \trans('torrent.name'),
            'seeders'         => \trans('torrent.seeders'),
            'leechers'        => \trans('torrent.leechers'),
            'times_completed' => \trans('torrent.completed-times'),
            'size'            => \trans('torrent.size'),
        ];
    }

    /**
     * Options for sort the search result by direction.
     *
     * @return array
     */
    public function direction(): array
    {
        return [
            'desc' => \trans('common.descending'),
            'asc'  => \trans('common.ascending'),
        ];
    }
}
