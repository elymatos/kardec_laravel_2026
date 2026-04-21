<?php

namespace App\Services;

use App\Database\Criteria;

class TimelineService
{
    // idTimeline
    // locale
    // id
    // title
    // text
    // docDate
    // link
    // group
    // subgroup
    // dtStart
    // dtEnd

    public static function getCategories()
    {
        try {
            $categories = Criteria::table('ak_timeline')
                ->select('group', 'subgroup')
                ->distinct()
                ->orderBy('group')
                ->orderBy('subgroup')
                ->get()
                ->groupBy('group')
                ->toArray();

            return $categories;
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";

            return '';
        }
    }

    public static function getTimelines(array $subgroup = [])
    {
        try {
            $keys = array_keys($subgroup);
            $criteria = Criteria::table('ak_timeline')
                ->select('idTimeline', 'locale', 'id', 'title', 'text', 'docDate', 'link', 'group', 'subgroup', 'dtStart', 'dtEnd');
            if (! empty($subgroup)) {
                $criteria->whereIn('subgroup', $keys);
            }
            $timelines = $criteria
                ->all();

            return $timelines;
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";

            return '';
        }
    }
}
