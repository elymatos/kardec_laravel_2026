<?php

namespace App\Services;

use App\Data\SearchData;
use App\Database\Criteria;
use Illuminate\Support\Facades\DB;

class SearchService
{
    private static function specialChars($str)
    {
        $specialChars = '!@#$%^&*()-_=+[{]};:\'",<.>/?\\|';

        return strpbrk($str, $specialChars) !== false;
    }

    public static function search(SearchData $data): array
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $items = [];
        $search = '';
        if (! is_null($data->idItem)) {
            $search .= "AND (it.idItem = {$data->idItem})";
        }
        if (! empty($data->idTag)) {
            $search .= "AND (t.idTag = {$data->idTag})";
        }
        if ($data->collectionCode != '') {
            $search .= "AND (it.codeCollection COLLATE utf8mb4_general_ci  = '{$data->collectionCode}')";
        }
        if ($data->year != '') {
            $search .= "AND (substr(it.docDate,7,4) = '{$data->year}')";
        }
        if (! empty($data->metadataType)) {
            $nameType = addslashes($data->metadataType);
            if (! empty($data->metadataInstanceId)) {
                $search .= "AND EXISTS (
                    SELECT 1 FROM view_ak_metadata m
                    WHERE m.idItem = it.idItem
                      AND m.nameType = '{$nameType}'
                      AND m.idInstance = {$data->metadataInstanceId}
                ) ";
            } else {
                $search .= "AND EXISTS (
                    SELECT 1 FROM view_ak_metadata m
                    WHERE m.idItem = it.idItem
                      AND m.nameType = '{$nameType}'
                ) ";
            }
        }
        if ($idLanguage == 1) {
            $items = DB::select("
            SELECT it.idItem, it.ptTitle as title, it.docDate, it.ptCollection as collection, t.idTag, t.ptName as tag
            FROM view_items it
            JOIN view_ak_item_tag t on (it.idItem = t.idItem)
            WHERE (it.idItem > 0)
            {$search}
        ");
        }
        if ($idLanguage == 2) {
            $items = DB::select("
            SELECT it.idItem, it.frTitle as title, s.text, it.docDate, it.frCollection as collection, t.idTag, t.ptName as tag
            FROM view_items it
            JOIN view_ak_item_tag t on (it.idItem = t.idItem)
            WHERE (it.idItem > 0)
            {$search}
        ");
        }
        $sentences = [];
        $hasQ = false;
        $words = [];
        $q = trim($data->search);
        if ($q != '') {
            $hasQ = true;
            if (str_contains($q, '"')) {
                $searchWords = "MATCH(text) AGAINST('{$q}') ";
                $words[] = str_replace('"', '', $q);
            } else {
                $words = explode(' ', $q);
                $searchWords = '';
                foreach ($words as $i => $word) {
                    $word = str_replace("'", "\'", $word);
                    $searchWords .= "MATCH(text) AGAINST('+{$word} * IN BOOLEAN MODE') ";
                }
            }
            $rows = DB::select("
                SELECT s.idItem, s.text
                FROM ak_sentence s
                WHERE {$searchWords} AND (s.idLanguage = {$idLanguage})
            ");
            foreach ($rows as $row) {
                $sentences[$row->idItem][] = $row;
            }
        }
        debug($sentences);
        $results = [];
        if (empty($sentences) and ! $hasQ) {
            foreach ($items as $item) {
                $item->sentences = [];
                $results[$item->idItem] = $item;
            }
        } else {
            foreach ($items as $item) {
                if (isset($sentences[$item->idItem])) {
                    $item->sentences = $sentences[$item->idItem];
                    $results[$item->idItem] = $item;
                }

            }
        }
        foreach ($results as $i => $result) {
            if (! empty($result->sentences)) {
                foreach ($result->sentences as $s => $sentence) {
                    $text = str_replace('’', "'", $sentence->text);
                    foreach ($words as $word) {
                        $result->sentences[$s]->text = preg_replace("/({$word})/i", "<span  class='ak-highlight-document'>$1</span>", $text);
                        //                        $results[$i]->tags = Criteria::table("view_ak_item_tag")
                        //                            ->where("idItem", $result->idItem)
                        //                            ->select("idTag", "ptName", "frName")
                        //                            ->all();
                    }
                }
            }
        }

        //        $final = collect($results)->groupBy("idItem")->toArray();
        //        debug($final);
        //        return $final;
        debug($results);

        return $results;
    }
}
