<?php

namespace App\Repositories;

use App\Database\Criteria;

class Item
{
    public static function byId(int $id): object
    {
        $item = Criteria::table('view_items')
            ->where('idItemType', 20)
            ->where('idItem', $id)
            ->first();

        return $item;
    }
}
