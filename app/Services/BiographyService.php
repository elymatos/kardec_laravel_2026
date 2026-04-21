<?php

namespace App\Services;

use App\Database\Criteria;
use GuzzleHttp\Client;

class BiographyService
{
    public static function list(string $lang = 'pt'): array
    {
        if ($lang == 'pt') {
            $result = Criteria::table('view_items')
                ->select('idItem', 'ptTitle as title')
                ->where('idItemType', 22)
                ->where('public', 1)
                ->orderBy('ptTitle')
                ->all();
        }
        if ($lang == 'fr') {
            $result = Criteria::table('view_items')
                ->select('idItem', 'frTitle as title')
                ->where('idItemType', 22)
                ->where('public', 1)
                ->orderBy('frTitle')
                ->all();
        }

        return $result;
    }

    public static function getItem(int $idItem, string $lang = 'pt')
    {
        $client = new Client([
            'base_uri' => env('OMEKA_URL'),
            'timeout' => 300.0,
        ]);
        try {
            $response = $client->request('GET', "items/get/{$idItem}?lang={$lang}");
            $item = json_decode($response->getBody());
            debug($item);

            return $item;
        } catch (\Exception $e) {
            debug($e->getMessage());

            return '';
        }
    }
}
