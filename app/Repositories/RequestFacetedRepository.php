<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Repositories;

use Illuminate\Support\Collection;
use App\Models\Category;
use App\Models\Type;

final class RequestFacetedRepository
{
    /**
     * Return a collection of Category Name from storage.
     *
     * @return Collection
     */
    public function categories(): \Illuminate\Support\Collection
    {
        return Category::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Return a collection of Type Name from storage.
     *
     * @return Collection
     */
    public function types(): \Illuminate\Support\Collection
    {
        return Type::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Options for sort the search result.
     *
     * @return array
     */
    public function sorting(): array
    {
        return [
            'created_at' => trans('torrent.date'),
            'name'       => trans('torrent.name'),
            'bounty'     => trans('request.bounty'),
            'votes'      => trans('request.votes'),
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
            'desc' => trans('common.descending'),
            'asc'  => trans('common.ascending'),
        ];
    }
}
