<?php

namespace App\Console\Commands\Ollama;

use App\Database\Criteria;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OllamaTranslateItemsCommand extends Command
{
    protected $signature = 'ollama:translate-items
                            {--model=aya-expanse:8b : Ollama model to use}
                            {--base-url= : Ollama API base URL (default: from config)}
                            {--lang= : Comma-separated target languages to translate (default: all)}
                            {--initial-id= : Start processing from this idItem}
                            {--only-missing : Only translate items where the target column is NULL}
                            {--item= : Process only this specific idItem (overrides --initial-id and --only-missing)}';

    protected $description = 'Translate ak_item.txtTranscription (French) into all target language columns using Ollama';

    /** @var array<string, string> */
    private array $languages = [
        'txtPT' => 'Portuguese',
        'txtEN' => 'English',
        'txtDE' => 'German',
        'txtIT' => 'Italian',
        'txtZH' => 'Chinese',
        'txtJP' => 'Japanese',
    ];

    public function handle(): int
    {
        $baseUrl = $this->option('base-url') ?? config('ollama.base_url');
        $model = $this->option('model');
        $timeout = config('ollama.timeout');
        $specificItem = $this->option('item') ? (int) $this->option('item') : null;
        $onlyMissing = $specificItem === null && $this->option('only-missing');

        $targetLanguages = $this->resolveTargetLanguages();
        if ($targetLanguages === null) {
            return self::FAILURE;
        }

        $this->info("Ollama URL : {$baseUrl}");
        $this->info("Model      : {$model}");
        $this->info('Languages  : '.implode(', ', array_keys($targetLanguages)));
        if ($specificItem !== null) {
            $this->info("Item       : {$specificItem} (single)");
        } else {
            $this->info('Only NULL  : '.($onlyMissing ? 'yes' : 'no'));
        }
        $this->newLine();

        if (! $this->verifyConnection($baseUrl, $timeout)) {
            return self::FAILURE;
        }

        $query = Criteria::table('ak_item')->whereNotNull('txtTranscription');

        if ($specificItem !== null) {
            $query->where('idItem', $specificItem);
        } elseif ($this->option('initial-id')) {
            $query->where('idItem', '>=', (int) $this->option('initial-id'));
        }

        $items = $query->all();
        $total = count($items);

        $this->info("Items to process: {$total}");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $errors = 0;

        foreach ($items as $item) {
            $source = trim((string) $item->txtTranscription);

            if ($source === '') {
                $bar->advance();

                continue;
            }

            $updates = [];

            foreach ($targetLanguages as $column => $language) {
                if ($onlyMissing && ! empty($item->{$column})) {
                    continue;
                }

                try {
                    $updates[$column] = $this->translate($baseUrl, $model, $timeout, $source, $language);
                } catch (Exception $e) {
                    $this->newLine();
                    $this->error("Item {$item->idItem} / {$language}: {$e->getMessage()}");
                    $errors++;
                }
            }

            if (! empty($updates)) {
                Criteria::table('ak_item')
                    ->where('idItem', $item->idItem)
                    ->update($updates);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($errors > 0) {
            $this->warn("Completed with {$errors} error(s).");

            return self::FAILURE;
        }

        $this->info('Translation completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>|null
     */
    private function resolveTargetLanguages(): ?array
    {
        $langOption = $this->option('lang');

        if (! $langOption) {
            return $this->languages;
        }

        $requested = array_map('strtoupper', explode(',', $langOption));
        $result = [];

        foreach ($requested as $code) {
            $column = 'txt'.ucfirst(strtolower($code));
            if (! isset($this->languages[$column])) {
                $this->error("Unknown language code: {$code}. Valid: PT, EN, DE, IT, ZH, JP");

                return null;
            }
            $result[$column] = $this->languages[$column];
        }

        return $result;
    }

    private function verifyConnection(string $baseUrl, int $timeout): bool
    {
        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}/api/version");

            if (! $response->successful()) {
                throw new Exception("HTTP {$response->status()}");
            }

            $version = $response->json('version') ?? 'unknown';
            $this->info("Ollama server ready (version: {$version})");

            return true;
        } catch (Exception $e) {
            $this->error("Cannot connect to Ollama at {$baseUrl}: {$e->getMessage()}");
            $this->line('Start Ollama with: ollama serve');

            return false;
        }
    }

    private function translate(
        string $baseUrl,
        string $model,
        int $timeout,
        string $text,
        string $targetLanguage,
    ): string {
        $response = Http::timeout($timeout)->post("{$baseUrl}/api/chat", [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate the given French text to {$targetLanguage}. Output only the translated text, no explanations or extra content. If the source text contains any HTML tags (e.g. <p>, <br>, <strong>, <em>, <a>, etc.), preserve them exactly as-is in the translated output — do not remove, escape, or alter them.",
                ],
                [
                    'role' => 'user',
                    'content' => $text,
                ],
            ],
            'stream' => false,
            'options' => [
                'temperature' => 0.2,
            ],
        ]);

        if (! $response->successful()) {
            throw new Exception("Ollama API error: {$response->body()}");
        }

        return trim($response->json('message.content') ?? '');
    }
}
