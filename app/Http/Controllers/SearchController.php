<?php

namespace App\Http\Controllers;

use App\Data\SearchData;
use App\Services\AppService;
use App\Services\SearchService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Middleware(name: 'web')]
class SearchController extends Controller
{
    #[Get(path: '/pesquisar')]
    public function search(): mixed
    {
        $language = AppService::getCurrentLanguageCode();

        $collections = $this->fetchCollections();
        $tags = $this->fetchTags($language);
        $metadataTypes = $this->fetchMetadataTypes();

        $view = $language === 'fr' ? 'Search.mainFr' : 'Search.mainPt';

        return view($view, compact('collections', 'tags', 'metadataTypes'));
    }

    #[Get(path: '/pesquisar/metadata/instancias')]
    public function metadataInstances(Request $request): string
    {
        $nameType = $request->query('metadataType', '');
        if (empty($nameType)) {
            return '<option value="">Todos</option>';
        }

        try {
            $instances = DB::connection('kardec')
                ->table('view_ak_metadata')
                ->select('idInstance', 'nameInstance')
                ->where('nameType', $nameType)
                ->distinct()
                ->orderBy('nameInstance')
                ->get();
        } catch (\Throwable) {
            $instances = collect();
        }

        $html = '<option value="">Todos</option>';
        foreach ($instances as $inst) {
            $html .= '<option value="'.e($inst->idInstance).'">'.e($inst->nameInstance).'</option>';
        }

        return $html;
    }

    private function fetchCollections(): Collection
    {
        try {
            return DB::connection('kardec')
                ->table('view_items')
                ->select('codeCollection', 'ptCollection')
                ->distinct()
                ->whereNotNull('codeCollection')
                ->whereRaw("codeCollection COLLATE utf8mb4_general_ci != ''")
                ->orderBy('ptCollection')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function fetchTags(string $lang): Collection
    {
        try {
            $nameCol = $lang === 'fr' ? 'frName' : 'ptName';

            return DB::connection('kardec')
                ->table('view_ak_item_tag')
                ->select('idTag', "{$nameCol} as name")
                ->distinct()
                ->orderBy($nameCol)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function fetchMetadataTypes(): Collection
    {
        try {
            return DB::connection('kardec')
                ->table('view_ak_metadata')
                ->select('nameType')
                ->distinct()
                ->whereNotNull('nameType')
                ->orderBy('nameType')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    #[Post(path: '/pesquisar')]
    public function searchBy(SearchData $data)
    {
        debug($data);
        $language = AppService::getCurrentLanguageCode();
        $results = SearchService::search($data);
        if ($language == 'pt') {
            $view = 'Search.resultPt';
        }
        if ($language == 'fr') {
            $view = 'Search.resultFr';
        }

        return view($view, [
            'results' => $results,
        ]);
    }
}
