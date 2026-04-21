<?php

namespace App\Offline\Documents;

use App\Database\Criteria;
use GuzzleHttp\Client;
use Html2Text\Html2Text;

class updateDocumentText
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
        Criteria::table('ak_sentence')
            ->where('idSentence', '>', 0)
            ->delete();
        $i = 1;
        foreach ($this->items as $item) {
            print_r($item->idItem."\n");
            try {
                $response = $client->request('GET', "items/get/{$item->idItem}?lang=pt");
                $body = json_decode($response->getBody());
                if (! is_null($body)) {
                    $translation = new Html2Text($body->traducao);
                    $txtTranslation = $translation->getText();

                    $fullTranslation = str_replace("\n", ' ', $txtTranslation);
                    $sentences = explode('.', $fullTranslation);
                    foreach ($sentences as $sentence) {
                        $sentence = trim(str_replace(['[', ']', '_', '#', '>', '<', '@', '%', '&', '"', '“', '/'], '', $sentence).'.');
                        if (strlen($sentence) > 3) {
                            //                        print_r($sentence ."\n");
                            Criteria::create('ak_sentence', [
                                'idSentence' => $i,
                                'idItem' => $item->idItem,
                                'text' => $sentence,
                                'idLanguage' => 1,
                            ]);
                            $i++;
                        }
                    }

                    $transcription = new Html2Text($body->transcricao);
                    $txtTranscription = $transcription->getText();

                    $fullTranscription = str_replace("\n", ' ', $txtTranscription);
                    $sentences = explode('.', $fullTranscription);
                    foreach ($sentences as $sentence) {
                        $sentence = trim(str_replace(['[', ']', '_', '#', '>', '<', '@', '%', '&', '"', '“', '/'], '', $sentence).'.');
                        if (strlen($sentence) > 3) {
                            //                        print_r($sentence ."\n");
                            Criteria::create('ak_sentence', [
                                'idSentence' => $i,
                                'idItem' => $item->idItem,
                                'text' => $sentence,
                                'idLanguage' => 2,
                            ]);
                            $i++;
                        }
                    }

                    Criteria::table('ak_item')
                        ->where('idItem', $item->idItem)
                        ->update([
                            'txtTranslation' => $txtTranslation,
                            'txtTranscription' => $txtTranscription,
                        ]);

                }
            } catch (\Exception $e) {
                echo $e->getMessage()."\n";
            }
        }
    }
}
