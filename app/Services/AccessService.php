<?php

namespace App\Services;

use App\Database\Criteria;

class AccessService
{
    public static function recent()
    {
        $result = Criteria::table('view_items')
            ->where('public', 1)
            ->whereIn('idItemType', [20, 21])
            ->orderBy('dtUpdatedOrder', 'DESC')
            ->orderBy('docDateOrder', 'DESC')
            ->treeResult('dtUpdated');

        return $result;
    }

    public static function year()
    {
        $result = Criteria::table('view_items')
            ->selectRaw('substr(docDateOrder,1,4) as year, ptTitle,frTitle,idItem,docDate')
            ->where('public', 1)
            ->whereIn('idItemType', [20, 21])
            ->orderBy('docDateOrder', 'DESC')
            ->treeResult('year');

        return $result;
    }

    public static function category(string $lang)
    {

        $result = Criteria::table('view_items as i')
            ->join('view_ak_item_tag as it', 'i.idItem', '=', 'it.idItem')
            ->select('it.ptName', 'it.frName', 'i.ptTitle', 'i.frTitle', 'i.idItem', 'i.docDate')
            ->where('public', 1)
            ->whereIn('idItemType', [20, 21]);
        if ($lang == 'pt') {
            $result = $result
                ->orderBy('ptName')
                ->treeResult('ptName');
        }
        if ($lang == 'fr') {
            $result = $result
                ->orderBy('frName')
                ->treeResult('frName');
        }

        return $result;
    }

    public static function collection()
    {
        $result = Criteria::table('view_items as i')
            ->join('omeka_element_texts as t', 'i.idCollection', '=', 't.record_id')
            ->selectRaw('substr(t.text,8) as collection, i.ptTitle,i.frTitle,i.idItem,i.docDate')
            ->where('t.record_type', 'Collection')
            ->where('t.element_id', 50)
            ->where('i.public', 1)
            ->whereIn('i.idItemType', [20, 21])
            ->orderByRaw(1)
            ->orderBy('i.idItem')
            ->treeResult('collection');

        return $result;
    }

    public static function id()
    {
        $result = Criteria::table('view_items')
            ->where('public', 1)
            ->whereIn('idItemType', [20, 21])
            ->orderBy('idItem', 'DESC')
            ->all();

        return $result;
    }
}
