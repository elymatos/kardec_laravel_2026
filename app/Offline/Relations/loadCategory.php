<?php

namespace App\Offline\Relations;

use App\Database\Criteria;
use Illuminate\Support\Facades\DB;

class loadCategory
{
    public array $items;

    public function __construct() {}

    public function process()
    {
        $categories = DB::select("
        select distinct t.name as nameType, i.name as nameInstance
from ak_entityrelation er
         join ak_relationtype r on (er.idRelationType = r.idRelationType)
         join ak_item m on (er.idEntity1 = m.idEntity)
         join ak_type t on (er.idEntity2 = t.idEntity)
         join ak_instance i on (er.idEntity3 = i.idEntity)
where (r.name = 'metadata')
        ");
        foreach ($categories as $category) {
            Criteria::function('type_instance_create(?,?)', [$category->nameType, $category->nameInstance]);
        }
    }
}
