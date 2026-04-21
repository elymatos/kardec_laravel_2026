<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use GuzzleHttp\Client;
use Html2Text\Html2Text;
use Illuminate\Console\Command;

class UpdateDocumentText extends Command
{
    protected $signature = 'app:update-document-text {--initial-id= : Initial idItem to start processing from}';

    protected $description = 'Update document text by fetching translations and transcriptions from Omeka API';

    public function handle(): int
    {
        $initialId = $this->option('initial-id');

        $query = Criteria::table('ak_item');
        if ($initialId) {
            $query->where('idItem', '>=', (int) $initialId);
        }
        $items = $query->all();

        $client = new Client([
            'base_uri' => config('services.omeka.url'),
            'timeout' => 300.0,
        ]);

        Criteria::table('ak_sentence')
            ->where('idSentence', '>=', (int) $initialId)
            ->delete();

        $sentenceId = 1;

        foreach ($items as $item) {
            $this->info("Processing item: {$item->idItem}");

            try {
                $response = $client->request('GET', "items/get/{$item->idItem}?lang=pt");
                $body = json_decode($response->getBody());

                if (is_null($body)) {
                    continue;
                }

                $sentenceId = $this->processTranslation($body, $item, $sentenceId);
                $sentenceId = $this->processTranscription($body, $item, $sentenceId);

                $translation = new Html2Text($body->traducao);
                $transcription = new Html2Text($body->transcricao);

                Criteria::table('ak_item')
                    ->where('idItem', $item->idItem)
                    ->update([
                        'txtTranslation' => $translation->getText(),
                        'txtTranscription' => $transcription->getText(),
                    ]);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        $this->info('Document text update completed.');

        return Command::SUCCESS;
    }

    private function processTranslation(object $body, object $item, int $sentenceId): int
    {
        $translation = new Html2Text($body->traducao);
        $txtTranslation = $translation->getText();
        $fullTranslation = str_replace("\n", ' ', $txtTranslation);
        $sentences = explode('.', $fullTranslation);

        foreach ($sentences as $sentence) {
            $sentenceId = $this->createSentence($sentence, $item->idItem, 1, $sentenceId);
        }

        return $sentenceId;
    }

    private function processTranscription(object $body, object $item, int $sentenceId): int
    {
        $transcription = new Html2Text($body->transcricao);
        $txtTranscription = $transcription->getText();
        $fullTranscription = str_replace("\n", ' ', $txtTranscription);
        $sentences = explode('.', $fullTranscription);

        foreach ($sentences as $sentence) {
            $sentenceId = $this->createSentence($sentence, $item->idItem, 2, $sentenceId);
        }

        return $sentenceId;
    }

    private function createSentence(string $sentence, int $idItem, int $idLanguage, int $sentenceId): int
    {
        $sentence = trim(str_replace(['[', ']', '_', '#', '>', '<', '@', '%', '&', '"', '"', '/'], '', $sentence).'.');

        if (strlen($sentence) > 3) {
            Criteria::create('ak_sentence', [
                'idSentence' => $sentenceId,
                'idItem' => $idItem,
                'text' => $sentence,
                'idLanguage' => $idLanguage,
            ]);
            $sentenceId++;
        }

        return $sentenceId;
    }
}
