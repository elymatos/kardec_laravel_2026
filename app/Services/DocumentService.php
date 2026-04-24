<?php

namespace App\Services;

use App\Database\Criteria;
use GuzzleHttp\Client;

class DocumentService
{
    // idItem
    //    public
    //    idItemType
    // idCollection
    // codeCollection
    // ptDescription
    // ptSubject
    // ptTitle
    // frDescription
    // frSubject
    // frTitle
    // dtPublished
    // dtPublishedOrder
    // dtUpdated
    // dtUpdatedOrder
    // docDateOrder
    // docDate
    // frCollection
    // ptCollection
    // docIndex

    public static function getItem(int $idItem, string $lang = 'pt')
    {
        try {
            $itemDb = Criteria::byId('view_items', 'idItem', $idItem);
            if (! $itemDb) {
                return '';
            }

            $akItem = Criteria::byId('ak_item', 'idItem', $idItem);

            $item = (object) [
                'idItem' => $idItem,
                'transcription' => self::textToHtml($akItem?->txtTranscription ?? ''),
                'around' => self::getAroundItems($idItem, $itemDb->docDateOrder, $lang),
                'tags' => [],
            ];

            if ($lang === 'pt') {
                $isAutoTranslation = empty($akItem?->txtTranslation) && ! empty($akItem?->txtPT);
                $rawTranslation = ! empty($akItem?->txtTranslation)
                    ? $akItem->txtTranslation
                    : ($akItem?->txtPT ?? '');
                $item->translation = $isAutoTranslation
                    ? self::autoTranslationNote().self::textToHtml($rawTranslation)
                    : self::textToHtml($rawTranslation);
                $item->title = $itemDb->ptTitle;
                $item->description = $itemDb->ptDescription;
                $item->collection = $itemDb->ptCollection;

                $snippets = Criteria::table('ak_snippet')->where('idItem', $idItem)->all();
                foreach ($snippets as $snippet) {
                    $links = '';
                    if ($snippet->link1 !== '') {
                        $links .= "<a class='links' href='{$snippet->link1}' target='_blank'>Sinopse biográfica</a>";
                    }
                    if ($snippet->link2 !== '') {
                        $links .= "<a class='links' href='{$snippet->link2}' target='_blank'>Mais informações</a>";
                    }
                    $snippetContent = "<span class='ak-inline'>{$snippet->text}<span class='content'>{$snippet->snippet}<br>{$links}</span></span>";
                    $item->translation = str_replace($snippet->text, $snippetContent, $item->translation);
                }
            }

            if ($lang === 'fr') {
                $item->translation = self::textToHtml($itemDb->translation ?? '');
                $item->title = $itemDb->frTitle;
                $item->description = $itemDb->frDescription;
                $item->collection = $itemDb->frCollection;
            }

            /** @var array<string,string> */
            $item->translations = [
                'en' => self::withAutoNote($akItem?->txtEN ?? ''),
                'de' => self::withAutoNote($akItem?->txtDE ?? ''),
                'it' => self::withAutoNote($akItem?->txtIT ?? ''),
                'zh' => self::withAutoNote($akItem?->txtZH ?? ''),
                'ja' => self::withAutoNote($akItem?->txtJP ?? ''),
            ];

            $item->docIndex = $itemDb->docIndex;
            $item->dtPublished = $itemDb->dtPublished;
            $item->dtUpdated = $itemDb->dtUpdated;
            $item->docDate = $itemDb->docDate;
            $item->files = self::getItemFiles($idItem);
            $item->production = Criteria::table('view_ak_production as p')
                ->where('p.idItem', $idItem)
                ->treeResult('nameType', 'nameInstance');
            $item->metadata = Criteria::table('view_ak_metadata as m')
                ->where('m.idItem', $idItem)
                ->chunkResult('nameType', 'nameInstance');
            $item->links = Criteria::table('view_ak_metadata_link as m')
                ->where('m.idItem', $idItem)
                ->all();
            $item->tags = Criteria::table('view_ak_item_tag')
                ->where('idItem', $idItem)
                ->select('idTag', 'ptName', 'frName')
                ->all();

            $user = AppService::getCurrentUser();
            if ($user) {
                $idUser = $user->idUser;
                $favorite = Criteria::table('ak_favorite')
                    ->where('idUser', $idUser)
                    ->where('idItem', $idItem)
                    ->first();
                $item->isFavorite = ! is_null($favorite);
            } else {
                $item->isFavorite = false;
            }

            return $item;
        } catch (\Exception $e) {
            debug($e->getMessage());

            return '';
        }
    }

    private static function autoTranslationNote(): string
    {
        return '<p class="text-amber-600 text-sm italic mb-3">⚠️ Este texto é uma tradução automática e pode conter erros e imprecisões.</p>';
    }

    private static function withAutoNote(string $text): string
    {
        $html = self::textToHtml($text);

        return $html !== '' ? self::autoTranslationNote().$html : '';
    }

    private static function textToHtml(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $paragraphs = preg_split('/\n{2,}/', trim($text)) ?: [];
        $html = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para !== '') {
                $html .= '<p>'.nl2br(htmlspecialchars($para, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).'</p>';
            }
        }

        return $html;
    }

    /**
     * @return array<int, object>
     */
    private static function getAroundItems(int $idItem, ?string $docDateOrder, string $lang): array
    {
        if (! $docDateOrder) {
            return [];
        }

        $titleCol = $lang === 'fr' ? 'frTitle' : 'ptTitle';

        $rows = Criteria::table('view_items')
            ->where('idItem', '!=', $idItem)
            ->whereNotNull('docDateOrder')
            ->orderByRaw('ABS(CAST(docDateOrder AS SIGNED) - CAST(? AS SIGNED))', [$docDateOrder])
            ->select('idItem', 'docDate', $titleCol)
            ->limit(10)
            ->all();

        return array_map(fn ($row) => (object) [
            'id' => $row->idItem,
            'date' => $row->docDate,
            'title' => $row->{$titleCol},
        ], $rows);
    }

    public static function getItemFiles(int $idItem)
    {
        $client = new Client([
            'base_uri' => env('OMEKA_URL'),
            'timeout' => 300.0,
        ]);
        try {
            $response = $client->request('GET', "api/files?item={$idItem}");
            $body = json_decode($response->getBody());
            $files = [];
            foreach ($body as $file) {
                if ($file->mime_type == 'image/jpeg') {
                    $output = [];
                    preg_match('/_(([0-9][0-9][0-9])[_|A-Z|a-z]?)/', $file->original_filename, $output);
                    $files[$output[2]] = $file->file_urls;
                }
            }
            ksort($files);

            return $files;
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function favorite(int $idItem): bool
    {
        $idUser = AppService::getCurrentUser()->idUser;
        $favorite = Criteria::table('ak_favorite')
            ->where('idUser', $idUser)
            ->where('idItem', $idItem)
            ->first();
        if (! is_null($favorite)) {
            Criteria::deleteById('ak_favorite', 'idFavorite', $favorite->idFavorite);

            return false;
        } else {
            Criteria::create('ak_favorite', [
                'idUser' => $idUser,
                'idItem' => $idItem,
            ]);

            return true;
        }
    }

    public static function getImages(string $lang = 'pt'): array
    {
        try {
            $images = [];
            if ($lang == 'pt') {
                debug('====');
                $images = Criteria::table('view_items')
                    ->where('idItemType', 6)
                    ->select('idItem', 'ptTitle as title', 'ptCollection as collection')
                    ->orderBy('ptTitle')
                    ->all();
            }
            if ($lang == 'fr') {
                $images = Criteria::table('view_items')
                    ->where('idItemType', 6)
                    ->select('idItem', 'frTitle as title', 'frCollection as collection')
                    ->orderBy('ptTitle')
                    ->all();
            }
            foreach ($images as $image) {
                $image->files = self::getImageFiles($image->idItem);
            }

            return $images;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getImageFiles(int $idItem)
    {
        $client = new Client([
            'base_uri' => env('OMEKA_URL'),
            'timeout' => 300.0,
        ]);
        try {
            $response = $client->request('GET', "api/files?item={$idItem}");
            $body = json_decode($response->getBody());

            return $body[0]->file_urls;
        } catch (\Exception $e) {
            return '';
        }
    }
}
