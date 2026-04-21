<?php

namespace App\Http\Controllers;

use App\Data\Timeline\UpdateData;
use App\Services\AppService;
use App\Services\TimelineService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware(name: 'web')]
class TimelineController extends Controller
{
    #[Get(path: '/timeline')]
    public function timeline()
    {
        AppService::setLocale();
        $locale = AppService::getLocale();
        $categories = TimelineService::getCategories();
        $timelines = TimelineService::getTimelines();

        return view('Timeline.main', [
            'categories' => $categories,
            'timelines' => $timelines,
        ]);
    }

    #[Post(path: '/timeline/update')]
    public function timelineUpdate(UpdateData $data)
    {
        debug($data);
        AppService::setLocale();
        $locale = AppService::getLocale();
        $timelines = TimelineService::getTimelines($data->subgroup);

        return view('Timeline.timeline', [
            'timelines' => $timelines,
        ]);
    }
}
