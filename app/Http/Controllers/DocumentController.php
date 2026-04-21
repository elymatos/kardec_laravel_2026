<?php

namespace App\Http\Controllers;

use App\Data\Document\ItemData;
use App\Services\AppService;
use App\Services\DocumentService;
use App\Services\TranslationService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

#[Middleware(name: 'web')]
class DocumentController extends Controller
{
    #[Get(path: '/item-pt')]
    public function itemPt(ItemData $data)
    {
        AppService::setLocale();
        $item = DocumentService::getItem($data->id, 'pt');

        return view('Document.itemPt', [
            'item' => $item,
            'locale' => AppService::getLocale(),
        ]);
    }

    #[Get(path: '/item-fr')]
    public function itemFr(ItemData $data)
    {
        AppService::setLocale();
        $item = DocumentService::getItem($data->id, 'fr');

        return view('Document.itemFr', [
            'item' => $item,
            'locale' => AppService::getLocale(),
        ]);
    }

    #[Post(path: '/favorite')]
    public function favorite(ItemData $data)
    {
        $favorite = DocumentService::favorite($data->id);
        $item = (object) [
            'idItem' => $data->id,
            'isFavorite' => $favorite,
        ];

        return view('Document.favorite', [
            'item' => $item,
        ]);
    }

    #[Get(path: '/item/{idItem}/translate/{lang}')]
    public function translate(int $idItem, string $lang): Response
    {
        if (! TranslationService::isSupported($lang)) {
            return response('<p><em>Idioma não suportado.</em></p>', 422)
                ->header('Content-Type', 'text/html');
        }

        $item = DocumentService::getItem($idItem, 'pt');

        if (! is_object($item) || empty($item->transcription)) {
            return response('<p><em>Texto de origem não disponível.</em></p>', 404)
                ->header('Content-Type', 'text/html');
        }

        $translated = TranslationService::translate($item->transcription, $lang);

        return response($translated, 200)
            ->header('Content-Type', 'text/html');
    }

    #[Get(path: '/item/{idItem}/citation')]
    public function citation(Request $request, int $idItem)
    {
        debug($request->style);
        $item = DocumentService::getItem($idItem, 'pt');

        return view('Document.citationDetail', [
            'style' => $request->style,
            'item' => $item,
        ]);
    }
}
