<?php

namespace App\Http\Controllers;

use App\Services\AccessService;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Support\Facades\App;

#[Middleware(name: 'web')]
class AccessController extends Controller
{
    #[Get(path: '/acesso/recente')]
    public function accessRecent()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = AccessService::recent();

        return view('Access.recent', [
            'list' => $list,
            'title' => 'recent',
            'lang' => $lang,
        ]);
    }

    #[Get(path: '/acesso/ano')]
    public function accessYear()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = AccessService::year();

        return view('Access.year', [
            'list' => $list,
            'title' => 'year',
            'lang' => $lang,
        ]);
    }

    #[Get(path: '/acesso/categoria')]
    public function accessCategory()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = AccessService::category($lang);

        return view('Access.category', [
            'list' => $list,
            'title' => 'category',
            'lang' => $lang,
        ]);
    }

    #[Get(path: '/acesso/acervo')]
    public function accessCollection()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = AccessService::collection();

        return view('Access.collection', [
            'list' => $list,
            'title' => 'collection',
            'lang' => $lang,
        ]);
    }

    #[Get(path: '/acesso/id')]
    public function accessId()
    {
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $list = AccessService::id();

        return view('Access.id', [
            'list' => $list,
            'title' => 'id',
            'lang' => $lang,
        ]);
    }
}
