<?php

namespace App\Offline\txtai;

use App\Database\Criteria;
use GuzzleHttp\Client;
use Html2Text\Html2Text;
use Illuminate\Support\Facades\DB;

class createDataset
{
    public array $items;

    public function __construct()
    {
        $this->items = Criteria::table('ak_item')
            ->all();
    }

    public function process()
    {
        $client = new Client([
            'base_uri' => env('OMEKA_URL'),
            'timeout' => 300.0,
        ]);
        $i = 1;
        foreach ($this->items as $item) {
            print_r($item->idItem."\n");
            try {
                $response = $client->request('GET', "items/get/{$item->idItem}?lang=pt");
                $body = json_decode($response->getBody());
                if (! is_null($body)) {
                    $translation = new Html2Text($body->traducao);
                    $txtTranslation = $translation->getText();
                    $txtTranslation = str_replace("\n", ' ', $txtTranslation);
                    $sentences = explode('.', $txtTranslation);
                    foreach ($sentences as $sentence) {
                        $sentence = trim(str_replace(['[', ']', '_', '#', '>', '<', '@', '%', '&', '"', '“', '/'], '', $sentence).'.');
                        if (strlen($sentence) > 3) {
                            //                        print_r($sentence ."\n");
                            DB::connection('dataset')->insert(
                                "insert into sentence(idSentence,idItem,sentence) values ({$i},{$item->idItem},'{$sentence}')"
                            );
                            $i++;
                        }
                    }
                }
            } catch (\Exception $e) {
                echo $e->getMessage()."\n";
            }
        }
    }
}
