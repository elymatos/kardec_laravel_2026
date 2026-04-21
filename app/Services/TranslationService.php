<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class TranslationService
{
    /** @var array<string, string> */
    private const LANGUAGE_NAMES = [
        'en' => 'English',
        'de' => 'German',
        'it' => 'Italian',
        'zh' => 'Chinese (Simplified)',
        'ja' => 'Japanese',
    ];

    /**
     * Translate an HTML manuscript text to the given target language.
     * The source text is 19th-century French. Preserve all HTML tags.
     *
     * @return string Translated HTML fragment, or error message on failure.
     */
    public static function translate(string $htmlText, string $targetLang): string
    {
        $langName = self::LANGUAGE_NAMES[$targetLang] ?? $targetLang;

        $systemPrompt = <<<PROMPT
            You are a scholarly translator specialising in 19th-century French texts related to Spiritism and the work of Allan Kardec.
            Translate the user-provided HTML fragment into {$langName}.
            Rules:
            - Preserve ALL HTML tags exactly as they appear (do not add, remove or alter any tag).
            - Keep proper nouns, place names and Spiritist technical terms unchanged unless a well-established translation exists.
            - Use formal, academic register appropriate for a digital humanities archive.
            - Output only the translated HTML — no explanations, no markdown fences.
            PROMPT;

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => trim($systemPrompt)],
                    ['role' => 'user',   'content' => $htmlText],
                ],
                'temperature' => 0.2,
                'max_tokens' => 4096,
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            return '<p><em>Erro na tradução: '.e($e->getMessage()).'</em></p>';
        }
    }

    public static function isSupported(string $lang): bool
    {
        return array_key_exists($lang, self::LANGUAGE_NAMES);
    }
}
