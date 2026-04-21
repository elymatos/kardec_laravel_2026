<?php

namespace App\Http\Controllers;

use App\Services\AppService;
use App\Services\BiographyService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

#[Middleware(name: 'web')]
class BiographyController extends Controller
{
    #[Get(path: '/biografias')]
    public function biografias()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = BiographyService::list($lang);

        return view('Biography.main', [
            'list' => $list,
        ]);
    }

    #[Get(path: '/biografias/item/{idItem}')]
    public function itemBiografia(int $idItem)
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $itemBio = BiographyService::getItem($idItem, $lang);

        return view('Biography.biography', [
            'itemBio' => $itemBio,
            'idItem' => $idItem,
        ]);
    }

    #[Get(path: '/biografias/item/{idItem}/fragment')]
    public function fragmentBiografia(int $idItem): View
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $itemBio = BiographyService::getItem($idItem, $lang);

        return view('Biography.biography-fragment', [
            'itemBio' => $itemBio,
            'idItem' => $idItem,
        ]);
    }

    #[Get(path: '/biografias/item/{idItem}/citation')]
    public function citation(Request $request, int $idItem)
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $itemBio = BiographyService::getItem($idItem, $lang);

        return view('Biography.citationDetail', [
            'style' => $request->style,
            'itemBio' => $itemBio,
            'idItem' => $idItem,
        ]);
    }
}
