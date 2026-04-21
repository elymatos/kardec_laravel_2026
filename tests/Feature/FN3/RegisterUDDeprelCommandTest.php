<?php

use App\Console\Commands\FN3\RegisterUDDeprelCommand;
use App\Services\Trankit\TrankitService;

/**
 * Tests for the helper methods of RegisterUDDeprelCommand.
 *
 * We test buildCharTokenMap, findTokensInSpan and findSpanRoot in isolation
 * by mocking TrankitService.handlePunct to return a predictable value.
 */

// ─── Helpers ────────────────────────────────────────────────────────────────

/**
 * Build a minimal $ud array matching the structure returned by parseSentenceRawTokens.
 * Each entry: [id, word, pos, ud, morph, lemma, rel, parent, children]
 *
 * @param  array<int, array{word: string, rel: string, parent: int}>  $tokens
 * @return array<int, array<string, mixed>>
 */
function makeUd(array $tokens): array
{
    $ud = [];
    foreach ($tokens as $idx => $t) {
        $key = $idx + 1;
        $ud[$key] = [
            'id' => $key,
            'word' => $t['word'],
            'pos' => 'NOUN',
            'ud' => '',
            'morph' => [],
            'lemma' => $t['word'],
            'rel' => $t['rel'],
            'parent' => $t['parent'],
            'children' => [],
        ];
    }

    return $ud;
}

/**
 * Call a protected method on the command via reflection.
 */
function callProtected(RegisterUDDeprelCommand $cmd, string $method, mixed ...$args): mixed
{
    $ref = new ReflectionMethod($cmd, $method);
    $ref->setAccessible(true);

    return $ref->invoke($cmd, ...$args);
}

// ─── Tests ──────────────────────────────────────────────────────────────────

describe('buildCharTokenMap', function () {
    it('maps simple sentence chars to correct UD token keys', function () {
        $sentence = 'O gato dorme.';
        // handlePunct would turn '.' into ' .' and return 'O gato dorme .'
        // surface tokens: ['O', 'gato', 'dorme', '.']
        // UD tokens (keys 1-4):
        //   1 => 'O', 2 => 'gato', 3 => 'dorme', 4 => '.'
        $ud = makeUd([
            ['word' => 'O', 'rel' => 'det', 'parent' => 2],
            ['word' => 'gato', 'rel' => 'nsubj', 'parent' => 3],
            ['word' => 'dorme', 'rel' => 'root', 'parent' => 0],
            ['word' => '.', 'rel' => 'punct', 'parent' => 3],
        ]);

        $trankitMock = Mockery::mock(TrankitService::class);
        $trankitMock->shouldReceive('handlePunct')
            ->with($sentence)
            ->andReturn('O gato dorme .');

        $cmd = new RegisterUDDeprelCommand;
        $charMap = callProtected($cmd, 'buildCharTokenMap', $sentence, $ud, $trankitMock);

        // 'O' is at char 0, maps to UD key 1
        expect($charMap[0])->toBe(1);
        // 'gato' starts at char 2, maps to UD key 2
        expect($charMap[2])->toBe(2);
        // 'dorme' starts at char 7, maps to UD key 3
        expect($charMap[7])->toBe(3);
        // '.' at char 12, maps to UD key 4
        expect($charMap[12])->toBe(4);
    });
});

describe('findTokensInSpan', function () {
    it('returns the single token key that overlaps a one-word span', function () {
        // charMap covering: char 0 => token 1, chars 2-5 => token 2, chars 7-11 => token 3
        $charMap = [0 => 1, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 7 => 3, 8 => 3, 9 => 3, 10 => 3, 11 => 3];

        $cmd = new RegisterUDDeprelCommand;
        $tokens = callProtected($cmd, 'findTokensInSpan', $charMap, 2, 5);

        expect($tokens)->toBe([2]);
    });

    it('returns multiple token keys for a multi-word span', function () {
        $charMap = [0 => 1, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 7 => 3, 8 => 3, 9 => 3, 10 => 3, 11 => 3];

        $cmd = new RegisterUDDeprelCommand;
        $tokens = callProtected($cmd, 'findTokensInSpan', $charMap, 0, 11);

        expect($tokens)->toContain(1)->toContain(2)->toContain(3);
    });

    it('returns empty array when no tokens overlap the span', function () {
        $charMap = [0 => 1, 2 => 2];

        $cmd = new RegisterUDDeprelCommand;
        $tokens = callProtected($cmd, 'findTokensInSpan', $charMap, 20, 25);

        expect($tokens)->toBeEmpty();
    });
});

describe('findSpanRoot', function () {
    it('identifies root of a single-token span', function () {
        $ud = makeUd([
            ['word' => 'gato', 'rel' => 'nsubj', 'parent' => 3],
        ]);

        $cmd = new RegisterUDDeprelCommand;
        [$word, $deprel] = callProtected($cmd, 'findSpanRoot', [1], $ud);

        expect($word)->toBe('gato');
        expect($deprel)->toBe('nsubj');
    });

    it('returns the token whose parent is outside the span for a multi-word span', function () {
        // "os grandes gatos dormem"
        // 1=os(det→2), 2=grandes(amod→3), 3=gatos(nsubj→4), 4=dormem(root→0)
        // FE span = tokens 1,2,3; root = token 3 (parent 4 is outside span)
        $ud = makeUd([
            ['word' => 'os', 'rel' => 'det', 'parent' => 2],
            ['word' => 'grandes', 'rel' => 'amod', 'parent' => 3],
            ['word' => 'gatos', 'rel' => 'nsubj', 'parent' => 4],
            ['word' => 'dormem', 'rel' => 'root', 'parent' => 0],
        ]);

        $cmd = new RegisterUDDeprelCommand;
        [$word, $deprel] = callProtected($cmd, 'findSpanRoot', [1, 2, 3], $ud);

        expect($word)->toBe('gatos');
        expect($deprel)->toBe('nsubj');
    });

    it('returns empty strings when the token list is empty', function () {
        $cmd = new RegisterUDDeprelCommand;
        [$word, $deprel] = callProtected($cmd, 'findSpanRoot', [], []);

        expect($word)->toBe('');
        expect($deprel)->toBe('');
    });

    it('handles a sentence-root token (parent 0) inside the span', function () {
        $ud = makeUd([
            ['word' => 'Dorme', 'rel' => 'root', 'parent' => 0],
        ]);

        $cmd = new RegisterUDDeprelCommand;
        [$word, $deprel] = callProtected($cmd, 'findSpanRoot', [1], $ud);

        expect($word)->toBe('Dorme');
        expect($deprel)->toBe('root');
    });
});
