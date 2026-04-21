<?php

namespace App\Http\Controllers;

use App\Services\AppService;
use App\Services\DocumentService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware(name: 'web')]
class ImagesController extends Controller
{
    #[Get(path: '/imagens')]
    public function accessRecent()
    {
        AppService::setLocale();
        $locale = AppService::getLocale();
        $images = DocumentService::getImages($locale);

        return view('Images.browse', [
            'images' => $images,
        ]);
    }
}
