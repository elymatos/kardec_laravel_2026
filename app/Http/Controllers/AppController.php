<?php

namespace App\Http\Controllers;

use App\Data\ContactData;
use App\Services\AppService;
use App\Services\DocumentService;
use App\Services\WordpressService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

#[Middleware(name: 'web')]
class AppController extends Controller
{
    #[Get(path: '/')]
    public function main()
    {
        debug(App::getLocale());
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $rows = DB::connection('kardec')
            ->table('view_items')
            ->select('idItem', 'docIndex', 'ptTitle', 'frTitle', 'ptCollection', 'frCollection')
            ->where('public', 1)
            ->whereIn('idItemType', [20, 21])
            ->orderBy('dtUpdatedOrder', 'desc')
            ->limit(8)
            ->get();

        $itemIds = $rows->pluck('idItem')->all();

        $thumbnails = DB::connection('kardec')
            ->table('omeka_files')
            ->select('item_id', 'filename')
            ->whereIn('item_id', $itemIds)
            ->where('has_derivative_image', 1)
            ->where('mime_type', 'like', 'image%')
            ->orderBy('id')
            ->get()
            ->unique('item_id')
            ->keyBy('item_id');

        $omekaUrl = config('services.omeka.url');

        $manuscripts = $rows->map(fn ($item) => (object) [
            'idItem'    => $item->idItem,
            'identifier' => $item->docIndex,
            'title'     => $lang === 'fr' ? $item->frTitle : $item->ptTitle,
            'acervo'    => (object) ['name' => $lang === 'fr' ? $item->frCollection : $item->ptCollection],
            'thumbnail' => isset($thumbnails[$item->idItem])
                ? $omekaUrl . '/files/thumbnails/' . $thumbnails[$item->idItem]->filename
                : null,
        ]);

        $lastPublicId = DB::connection('kardec')
            ->table('view_items')
            ->where('public', 1)
            ->orderBy('idItem', 'desc')
            ->value('idItem');

        $viewerItem = $lastPublicId ? DocumentService::getItem((int) $lastPublicId, $lang) : null;

        $timelineCategories = collect([
            (object) ['label' => 'Cartas',            'color' => '#b8892a', 'left' => 0, 'width' => 45],
            (object) ['label' => 'Dissertações',      'color' => '#7a5c1e', 'left' => 10, 'width' => 30],
            (object) ['label' => 'Notas autógrafas',  'color' => '#2a7a5c', 'left' => 5, 'width' => 55],
            (object) ['label' => 'Comunicações esp.', 'color' => '#2a4a7a', 'left' => 20, 'width' => 40],
            (object) ['label' => 'Fragmentos',        'color' => '#7a2a4a', 'left' => 30, 'width' => 25],
            (object) ['label' => 'Obras publicadas',  'color' => '#4a4a7a', 'left' => 40, 'width' => 30],
        ]);

        $timelineEvents = collect([
            (object) ['year' => '1855', 'title' => 'Carta a Camille Flammarion',                  'acervo' => 'Acervo BNF',          'category' => 'Carta',           'color' => '#b8892a', 'id' => null],
            (object) ['year' => '1857', 'title' => 'O Livro dos Espíritos — 1ª edição',           'acervo' => 'Biblioteca Nacional', 'category' => 'Obra publicada',  'color' => '#4a4a7a', 'id' => null],
            (object) ['year' => '1858', 'title' => 'Dissertação sobre a natureza dos espíritos',  'acervo' => 'Acervo FEAL',         'category' => 'Dissertação',     'color' => '#7a5c1e', 'id' => null],
            (object) ['year' => '1860', 'title' => 'Nota autógrafa nº 12',                        'acervo' => 'Acervo Gauthier',     'category' => 'Nota autógrafa',  'color' => '#2a7a5c', 'id' => null],
            (object) ['year' => '1861', 'title' => 'O Livro dos Médiuns',                         'acervo' => 'Biblioteca Nacional', 'category' => 'Obra publicada',  'color' => '#4a4a7a', 'id' => null],
            (object) ['year' => '1863', 'title' => 'Comunicação espírita — sessão de março',      'acervo' => 'Acervo BNF',          'category' => 'Comunicação',     'color' => '#2a4a7a', 'id' => null],
            (object) ['year' => '1864', 'title' => 'O Evangelho Segundo o Espiritismo',           'acervo' => 'Biblioteca Nacional', 'category' => 'Obra publicada',  'color' => '#4a4a7a', 'id' => null],
            (object) ['year' => '1867', 'title' => 'Fragmento autógrafo — correspondência',       'acervo' => 'Acervo FEAL',         'category' => 'Fragmento',       'color' => '#7a2a4a', 'id' => null],
            (object) ['year' => '1869', 'title' => 'Última carta — Henri Sausse',                 'acervo' => 'Acervo Gauthier',     'category' => 'Carta',           'color' => '#b8892a', 'id' => null],
        ]);

        $biographies = collect([
            (object) ['name' => 'Allan Kardec',       'role' => 'Autor principal · 1804–1869'],
            (object) ['name' => 'Camille Flammarion', 'role' => 'Astrônomo e colaborador'],
            (object) ['name' => 'Henri Sausse',       'role' => 'Editor e biógrafo'],
            (object) ['name' => 'Gabriel Delanne',    'role' => 'Cientista espírita · 1857–1926'],
            (object) ['name' => 'Léon Denis',         'role' => 'Continuador da obra · 1846–1927'],
            (object) ['name' => 'Hippolyte Rivail',   'role' => 'Nome civil de Allan Kardec'],
            (object) ['name' => 'Ernest Bersot',      'role' => 'Filósofo e correspondente'],
            (object) ['name' => 'Marie Boudet',       'role' => 'Médium e colaboradora'],
        ]);

        //        $page = WordpressService::getPage("index", $lang);
        return view('App.main', [
            //            'page' => $page,
            'title' => 'index',
            'manuscripts' => $manuscripts,
            'viewerItem' => $viewerItem,
            'timelineCategories' => $timelineCategories,
            'timelineEvents' => $timelineEvents,
            'biographies' => $biographies,
        ]);
    }

    #[Get(path: '/apresentacao')]
    public function presentation(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('presentation', $lang);
        //
        //        return view('App.presentation', ['content' => $content]);
        return view('App.presentation');
    }

    #[Get(path: '/acervos')]
    public function collection(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('collections', $lang);
        //
        //        return view('App.collection-info', ['content' => $content]);
        return view('App.collection-info');
    }

    #[Get(path: '/politicaeditorial')]
    public function editorial(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('editorial', $lang);
        //
        //        return view('App.editorial', ['content' => $content]);
        return view('App.editorial');
    }

    #[Get(path: '/equipe')]
    public function team(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('team', $lang);
        //
        //        return view('App.team', ['content' => $content]);
        return view('App.team');
    }

    #[Get(path: '/condicoesdeuso')]
    public function terms(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('terms', $lang);
        //
        //        return view('App.terms', ['content' => $content]);
        return view('App.terms');
    }

    #[Get(path: '/bibliografia')]
    public function bibliography(): mixed
    {
        //        $lang = AppService::getCurrentLanguageCode();
        //        App::setLocale($lang);
        //        $content = $this->fetchWordpressPage('bibliography', $lang);
        //
        //        return view('App.bibliography', ['content' => $content]);
        return view('App.bibliography');
    }

    private function fetchWordpressPage(string $page, string $lang): ?string
    {
        try {
            return WordpressService::getPage($page, $lang);
        } catch (\Throwable) {
            return null;
        }
    }

    #[Get(path: '/contato')]
    public function contato()
    {
        $token = md5(uniqid(rand(), true));
        session(['mail_token' => $token]);
        $lang = AppService::getCurrentLanguageCode();
        App::setLocale($lang);
        $view = 'App.contactPt';
        if ($lang == 'fr') {
            $view = 'App.contactFr';
        }

        return view($view, [
            'token' => $token,
        ]);
    }

    #[Post(path: '/contato')]
    public function contatoPost(ContactData $data)
    {
        debug($data);
        $token = session('mail_token');
        if ($data->token == $token) {
            return view('App.contactPost', $data);
        } else {
            return $this->renderNotify('error', 'Token not valid.');
        }
    }

    #[Get(path: '/changeLanguage/{language}')]
    public function changeLanguage(Request $request, string $language)
    {
        $currentURL = $request->header('Hx-Current-Url');
        $data = DB::connection('kardec')
            ->table('ak_language')
            ->where('language', '=', $language)
            ->first();
        AppService::setCurrentLanguage($data->idLanguage);
        debug(AppService::getCurrentLanguage());

        return $this->redirect($currentURL);
    }
}
