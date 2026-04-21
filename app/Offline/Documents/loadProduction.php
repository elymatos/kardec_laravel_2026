<?php

namespace App\Offline\Documents;

use App\Database\Criteria;

class loadProduction
{
    public array $items;

    public function __construct() {}

    public function process()
    {
        $productions = Criteria::table('temp_production')
            ->all();
        foreach ($productions as $production) {
            Criteria::function('production_create(?,?,?)', [$production->idItem, $production->type, $production->instance]);
        }
    }
}
