<?php

use App\Services\Parser\SingleSpreadActivationService;
use App\Services\Parser\SpreadActivationService;
use Illuminate\Support\Facades\DB;

/**
 * Helper to build a word array matching InputParserService format.
 */
function makeSingleWord(int $position, string $word, string $pos, string $lemma = ''): array
{
    return [
        'position' => $position,
        'word' => $word,
        'lemma' => $lemma ?: $word,
        'pos' => $pos,
        'deprel' => 'root',
        'head' => -1,
        'morph' => [],
        'children' => [],
        'features' => [],
    ];
}

beforeEach(function () {
    $nodeCount = DB::table('parser_pattern_node')->count();

    if ($nodeCount === 0) {
        $this->artisan('parser:build-grammar-graph --grammar=1');
    }
});

// --- END node span override ---

it('produces fewer END parser nodes than the standard service for optional elements', function () {
    $words = [
        makeSingleWord(0, 'a', 'DET'),
        makeSingleWord(1, 'casa', 'NOUN'),
    ];

    $standardResult = app(SpreadActivationService::class)->activate($words, 1);
    $singleResult = app(SingleSpreadActivationService::class)->activate($words, 1);

    $standardCount = collect($standardResult['nodes'])->filter(fn ($n) => $n->type === 'END')->count();
    $singleCount = collect($singleResult['nodes'])->filter(fn ($n) => $n->type === 'END')->count();

    expect($singleCount)->toBeLessThan($standardCount);
});

it('END nodes for the same grammar node have non-overlapping spans', function () {
    $words = [
        makeSingleWord(0, 'a', 'DET'),
        makeSingleWord(1, 'casa', 'NOUN'),
    ];

    $result = app(SingleSpreadActivationService::class)->activate($words, 1);

    $endNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'END');

    // Group by grammar node ID
    $byGrammar = $endNodes->groupBy('grammarNodeId');

    foreach ($byGrammar as $grammarNodeId => $nodes) {
        $nodeList = $nodes->values()->toArray();

        for ($i = 0; $i < count($nodeList); $i++) {
            for ($j = $i + 1; $j < count($nodeList); $j++) {
                $a = $nodeList[$i];
                $b = $nodeList[$j];

                // Neither span should be strictly contained in the other
                $aContainsB = $a->spanStart <= $b->spanStart && $b->spanEnd <= $a->spanEnd
                    && ($a->spanStart < $b->spanStart || $b->spanEnd < $a->spanEnd);
                $bContainsA = $b->spanStart <= $a->spanStart && $a->spanEnd <= $b->spanEnd
                    && ($b->spanStart < $a->spanStart || $a->spanEnd < $b->spanEnd);

                expect($aContainsB)->toBeFalse(
                    "END node for grammar node {$grammarNodeId}: span [{$a->spanStart},{$a->spanEnd}] strictly contains [{$b->spanStart},{$b->spanEnd}]"
                );
                expect($bContainsA)->toBeFalse(
                    "END node for grammar node {$grammarNodeId}: span [{$b->spanStart},{$b->spanEnd}] strictly contains [{$a->spanStart},{$a->spanEnd}]"
                );
            }
        }
    }
});

// --- Completed constructions correctness ---

it('produces correct widest-span completions for DET+NOUN', function () {
    $words = [
        makeSingleWord(0, 'a', 'DET'),
        makeSingleWord(1, 'casa', 'NOUN'),
    ];

    $result = app(SingleSpreadActivationService::class)->activate($words, 1);

    $signatures = collect($result['completedConstructions'])
        ->map(fn ($c) => "{$c['constructionName']}:{$c['spanStart']}-{$c['spanEnd']}")
        ->sort()
        ->values()
        ->toArray();

    // Span override collapses [0,0] and [1,1] variants into widest span [0,1]
    expect($signatures)->toBe(['DET:0-0', 'NOUN:1-1', 'REF:0-1', 'REF_BASE:0-1']);
});

it('produces correct widest-span completions for DET+NOUN+VERB', function () {
    $words = [
        makeSingleWord(0, 'a', 'DET'),
        makeSingleWord(1, 'casa', 'NOUN'),
        makeSingleWord(2, 'caiu', 'VERB'),
    ];

    $result = app(SingleSpreadActivationService::class)->activate($words, 1);

    $signatures = collect($result['completedConstructions'])
        ->map(fn ($c) => "{$c['constructionName']}:{$c['spanStart']}-{$c['spanEnd']}")
        ->sort()
        ->values()
        ->toArray();

    // Full-sentence CLAUSE and widest REF span; intermediate-only CLAUSE:2-2 collapsed
    expect($signatures)->toBe(['CLAUSE:0-2', 'DET:0-0', 'NOUN:1-1', 'PRED:2-2', 'REF:0-1', 'REF_BASE:0-1', 'VERB:2-2']);
});

it('completes NOUN and REF for single word', function () {
    $words = [makeSingleWord(0, 'casa', 'NOUN')];
    $result = app(SingleSpreadActivationService::class)->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('REF');
});

it('completes CLAUSE for DET+NOUN+VERB', function () {
    $words = [
        makeSingleWord(0, 'a', 'DET'),
        makeSingleWord(1, 'casa', 'NOUN'),
        makeSingleWord(2, 'caiu', 'VERB'),
    ];
    $result = app(SingleSpreadActivationService::class)->activate($words, 1);

    $clause = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'CLAUSE' && $c['spanStart'] === 0 && $c['spanEnd'] === 2);
    expect($clause)->not->toBeNull();
});

it('returns empty completions for empty word list', function () {
    $result = app(SingleSpreadActivationService::class)->activate([], 1);
    expect($result['completedConstructions'])->toBeEmpty();
});
