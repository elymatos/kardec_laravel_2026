<?php

use App\Services\Parser\InputParserService;

describe('ssd:pre-parser-sentence', function () {
    it('fails when --output-path is not provided', function () {
        $this->artisan('ssd:pre-parser-sentence')
            ->assertFailed();
    });

    it('fails when the parser service is unavailable', function () {
        $this->mock(InputParserService::class)
            ->shouldReceive('isAvailable')
            ->once()
            ->andReturn(false);

        $this->artisan('ssd:pre-parser-sentence', ['--output-path' => '/tmp/test_pre_parser.txt'])
            ->assertFailed();
    });

    it('classifies words and writes tag sequences to the output file', function () {
        $outputPath = tempnam(sys_get_temp_dir(), 'pre_parser_test_');

        // Words covering the main reclassification paths:
        // - lemma literal match (NO)
        // - SLOT+constraint match (FIN, PPER)
        // - SLOT no-constraint match (NOUN)
        // - unknown POS fallback (PUNCT)
        $parsedWords = [
            ['position' => 0, 'word' => 'Não', 'lemma' => 'não', 'pos' => 'ADV', 'morph' => [], 'deprel' => 'advmod', 'head' => 2, 'children' => [], 'features' => []],
            ['position' => 1, 'word' => 'sei', 'lemma' => 'saber', 'pos' => 'VERB', 'morph' => ['VerbForm' => 'Fin'], 'deprel' => 'ROOT', 'head' => 0, 'children' => [], 'features' => []],
            ['position' => 2, 'word' => 'eu', 'lemma' => 'eu', 'pos' => 'PRON', 'morph' => ['PronType' => 'Prs'], 'deprel' => 'nsubj', 'head' => 1, 'children' => [], 'features' => []],
            ['position' => 3, 'word' => 'casa', 'lemma' => 'casa', 'pos' => 'NOUN', 'morph' => [], 'deprel' => 'obj', 'head' => 1, 'children' => [], 'features' => []],
            ['position' => 4, 'word' => '.', 'lemma' => '.', 'pos' => 'PUNCT', 'morph' => [], 'deprel' => 'punct', 'head' => 1, 'children' => [], 'features' => []],
        ];

        $this->mock(InputParserService::class)
            ->shouldReceive('isAvailable')
            ->andReturn(true)
            ->shouldReceive('parse')
            ->andReturn($parsedWords);

        $this->artisan('ssd:pre-parser-sentence', [
            '--output-path' => $outputPath,
            '--grammar' => 1,
            '--limit' => 1,
        ])->assertSuccessful();

        $content = file_get_contents($outputPath);
        $lines = array_filter(explode("\n", trim($content)));

        expect($lines)->toHaveCount(1);

        $tags = explode(' ', reset($lines));

        // "não" lemma → NO (literal match takes priority over POS)
        expect($tags[0])->toBe('NO');
        // VERB + VerbForm=Fin → FIN
        expect($tags[1])->toBe('FIN');
        // PRON + PronType=Prs → PPER
        expect($tags[2])->toBe('PPER');
        // NOUN (no constraint) → NOUN
        expect($tags[3])->toBe('NOUN');
        // PUNCT has no grammar rule → fallback to original POS
        expect($tags[4])->toBe('PUNCT');

        @unlink($outputPath);
    });

    it('writes one tag sequence per sentence', function () {
        $outputPath = tempnam(sys_get_temp_dir(), 'pre_parser_multi_');

        $words = [
            ['position' => 0, 'word' => 'João', 'lemma' => 'João', 'pos' => 'PROPN', 'morph' => [], 'deprel' => 'ROOT', 'head' => 0, 'children' => [], 'features' => []],
        ];

        $this->mock(InputParserService::class)
            ->shouldReceive('isAvailable')
            ->andReturn(true)
            ->shouldReceive('parse')
            ->andReturn($words);

        $this->artisan('ssd:pre-parser-sentence', [
            '--output-path' => $outputPath,
            '--grammar' => 1,
            '--limit' => 3,
        ])->assertSuccessful();

        $content = file_get_contents($outputPath);
        $lines = array_filter(explode("\n", trim($content)));

        // 3 sentences processed → 3 lines
        expect(count($lines))->toBe(3);

        // PROPN maps to NOUN construction (pattern allows NOUN | PROPN)
        foreach ($lines as $line) {
            expect($line)->toBe('NOUN');
        }

        @unlink($outputPath);
    });

    it('skips sentences that return no words and continues', function () {
        $outputPath = tempnam(sys_get_temp_dir(), 'pre_parser_skip_');

        $this->mock(InputParserService::class)
            ->shouldReceive('isAvailable')
            ->andReturn(true)
            ->shouldReceive('parse')
            ->andReturn([]);

        $this->artisan('ssd:pre-parser-sentence', [
            '--output-path' => $outputPath,
            '--grammar' => 1,
            '--limit' => 2,
        ])->assertSuccessful();

        $content = file_get_contents($outputPath);
        expect(trim($content))->toBe('');

        @unlink($outputPath);
    });
});
