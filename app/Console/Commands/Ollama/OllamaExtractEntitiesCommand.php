<?php

namespace App\Console\Commands\Ollama;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OllamaExtractEntitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ollama:extract-entities
                            {--model= : The Ollama model to use (default: from .env)}
                            {--base-url= : Ollama API base URL (default: from .env)}
                            {--limit= : Limit number of files to process (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract named entities (places, organizations, events) from text files using Ollama LLM';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Named Entity Recognition...');
        $this->newLine();

        $baseUrl = $this->option('base-url') ?? env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $model = $this->option('model') ?? env('OLLAMA_DEFAULT_MODEL', 'llama3.1:8b');
        $timeout = 60; // Longer timeout for text processing
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info("Base URL: {$baseUrl}");
        $this->info("Using model: {$model}");
        $this->info("Timeout: {$timeout}s");
        if ($limit) {
            $this->info("Processing limit: {$limit} files");
        }
        $this->newLine();

        try {
            // Check Ollama server connection
            $this->testConnection($baseUrl, $timeout);

            // Get all .txt files
            $inputDir = base_path('temp/links');
            $txtFiles = glob($inputDir.'/*.txt');

            if (empty($txtFiles)) {
                $this->error("No .txt files found in {$inputDir}");

                return 1;
            }

            $totalFiles = count($txtFiles);
            if ($limit) {
                $txtFiles = array_slice($txtFiles, 0, $limit);
                $this->info("Found {$totalFiles} files, processing first {$limit}...");
            } else {
                $this->info("Found {$totalFiles} files to process");
            }
            $this->newLine();

            // Process each file
            $successCount = 0;
            $errorCount = 0;
            $bar = $this->output->createProgressBar(count($txtFiles));
            $bar->start();

            foreach ($txtFiles as $txtFile) {
                $filename = basename($txtFile);
                $id = pathinfo($filename, PATHINFO_FILENAME);

                try {
                    // Read and clean the text
                    $rawContent = file_get_contents($txtFile);
                    $decodedContent = html_entity_decode($rawContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleanText = strip_tags($decodedContent);
                    $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

                    if (empty($cleanText)) {
                        $this->warn("\nSkipping empty file: {$filename}");
                        $bar->advance();

                        continue;
                    }

                    // Extract entities using LLM
                    $entities = $this->extractEntities($baseUrl, $model, $timeout, $cleanText);

                    // Write to CSV
                    $csvFile = $inputDir."/{$id}.csv";
                    $this->writeCSV($csvFile, $id, $entities);

                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $this->newLine();
                    $this->error("Error processing {$filename}: ".$e->getMessage());
                    $this->newLine();
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Summary
            $this->info('✅ Processing completed!');
            $this->info("   Successful: {$successCount}");
            if ($errorCount > 0) {
                $this->warn("   Errors: {$errorCount}");
            }

        } catch (Exception $e) {
            $this->error('❌ Fatal error: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    private function testConnection(string $baseUrl, int $timeout): void
    {
        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}/api/version");

            if ($response->successful()) {
                $version = $response->json('version') ?? 'unknown';
                $this->line("✅ Ollama server is running (version: {$version})");
            } else {
                throw new Exception("Server returned status {$response->status()}");
            }
        } catch (Exception $e) {
            $this->error("❌ Cannot connect to Ollama server at {$baseUrl}");
            $this->line('Make sure Ollama is running with: ollama serve');
            throw $e;
        }

        $this->newLine();
    }

    private function extractEntities(string $baseUrl, string $model, int $timeout, string $text): array
    {
        // Truncate very long texts to avoid token limits
        $maxLength = 4000;
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength).'...';
        }

        $prompt = <<<PROMPT
Extract named entities from this Portuguese historical text about Spiritism from the 19th century.

Entity types to extract:
- PLACE: Cities, countries, addresses, locations, buildings, geographical locations
- ORGANIZATION: Societies, groups, institutions, associations, circles
- EVENT: Historical events, meetings, sessions, gatherings

Important:
- Only extract entities that are clearly present in the text
- Return entities in their original form (as they appear in the text)
- Each entity should be unique (no duplicates)
- Do not extract common words or generic terms

Text:
{$text}

Return ONLY a valid JSON array with this exact format (no additional text):
[{"entity": "Paris", "type": "PLACE"}, {"entity": "Sociedade Espírita", "type": "ORGANIZATION"}]

If no entities are found, return an empty array: []
PROMPT;

        $response = Http::timeout($timeout)->post("{$baseUrl}/api/chat", [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a specialized assistant for Named Entity Recognition in Portuguese historical texts. You must respond ONLY with valid JSON format, no additional text or explanation.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'stream' => false,
            'options' => [
                'temperature' => 0.3, // Lower temperature for more consistent output
            ],
        ]);

        if (! $response->successful()) {
            throw new Exception("LLM request failed: {$response->body()}");
        }

        $data = $response->json();
        $content = $data['message']['content'] ?? '';

        // Clean the response - sometimes LLM adds markdown code blocks
        $content = trim($content);
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        $content = trim($content);

        // Parse JSON
        $entities = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: '.json_last_error_msg().' - Response: '.substr($content, 0, 200));
        }

        if (! is_array($entities)) {
            throw new Exception('Expected array, got: '.gettype($entities));
        }

        return $entities;
    }

    private function writeCSV(string $csvFile, string $id, array $entities): void
    {
        $handle = fopen($csvFile, 'w');

        if ($handle === false) {
            throw new Exception("Cannot open file for writing: {$csvFile}");
        }

        // Write header
        fputcsv($handle, ['id', 'entity', 'type']);

        // Write entities
        foreach ($entities as $entity) {
            if (! isset($entity['entity']) || ! isset($entity['type'])) {
                continue; // Skip malformed entries
            }

            fputcsv($handle, [
                $id,
                $entity['entity'],
                $entity['type'],
            ]);
        }

        fclose($handle);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }
}
