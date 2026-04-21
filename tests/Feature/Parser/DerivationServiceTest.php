<?php

use App\Services\Parser\DerivationService;
use App\Services\Parser\SpreadActivationService;
use Illuminate\Support\Facades\DB;

/**
 * Helper to build a word array matching InputParserService format.
 */
function makeDerivWord(int $position, string $word, string $pos, string $lemma = ''): array
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

// --- Leaf constructions produce no children ---

it('produces a leaf derivation node for a single NOUN word', function () {
    $activation = app(SpreadActivationService::class);
    $words = [makeDerivWord(0, 'casa', 'NOUN')];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'NOUN');

    expect($trees)->not->toBeEmpty();
    $noun = $trees[0];
    expect($noun['constructionName'])->toBe('NOUN');
    expect($noun['spanStart'])->toBe(0);
    expect($noun['spanEnd'])->toBe(0);
    expect($noun['text'])->toBe('casa');
    expect($noun['children'])->toBeEmpty();
});

// --- Single-word REF builds its tree from sub-constructions ---

it('builds REF → REF_BASE → NOUN derivation for single NOUN word', function () {
    $activation = app(SpreadActivationService::class);
    $words = [makeDerivWord(0, 'casa', 'NOUN')];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'REF');

    expect($trees)->not->toBeEmpty();
    $ref = $trees[0];
    expect($ref['constructionName'])->toBe('REF');
    expect($ref['spanStart'])->toBe(0);
    expect($ref['spanEnd'])->toBe(0);

    // REF → REF_BASE
    $refBase = collect($ref['children'])->first(fn ($c) => $c['constructionName'] === 'REF_BASE');
    expect($refBase)->not->toBeNull();
    expect($refBase['spanStart'])->toBe(0);
    expect($refBase['spanEnd'])->toBe(0);

    // REF_BASE → NOUN
    $noun = collect($refBase['children'])->first(fn ($c) => $c['constructionName'] === 'NOUN');
    expect($noun)->not->toBeNull();
    expect($noun['text'])->toBe('casa');
});

// --- Two-word REF: DET + NOUN ---

it('builds REF → REF_BASE → {DET, NOUN} derivation for "a casa"', function () {
    $activation = app(SpreadActivationService::class);
    $words = [
        makeDerivWord(0, 'a', 'DET'),
        makeDerivWord(1, 'casa', 'NOUN'),
    ];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'REF');

    // Pick the widest REF tree (spanning both words)
    $ref = collect($trees)->first(fn ($t) => $t['spanStart'] === 0 && $t['spanEnd'] === 1);
    expect($ref)->not->toBeNull();
    expect($ref['constructionName'])->toBe('REF');
    expect($ref['text'])->toBe('a casa');

    $refBase = collect($ref['children'])->first(fn ($c) => $c['constructionName'] === 'REF_BASE');
    expect($refBase)->not->toBeNull();
    expect($refBase['spanStart'])->toBe(0);
    expect($refBase['spanEnd'])->toBe(1);

    $childNames = collect($refBase['children'])->pluck('constructionName');
    expect($childNames)->toContain('DET');
    expect($childNames)->toContain('NOUN');

    $det = collect($refBase['children'])->first(fn ($c) => $c['constructionName'] === 'DET');
    expect($det['spanStart'])->toBe(0);
    expect($det['spanEnd'])->toBe(0);
    expect($det['text'])->toBe('a');

    $noun = collect($refBase['children'])->first(fn ($c) => $c['constructionName'] === 'NOUN');
    expect($noun['spanStart'])->toBe(1);
    expect($noun['spanEnd'])->toBe(1);
    expect($noun['text'])->toBe('casa');
});

// --- Three-word CLAUSE: DET NOUN VERB ---

it('builds CLAUSE → {SUBJECT, PRED} derivation for "a casa caiu"', function () {
    $activation = app(SpreadActivationService::class);
    $words = [
        makeDerivWord(0, 'a', 'DET'),
        makeDerivWord(1, 'casa', 'NOUN'),
        makeDerivWord(2, 'caiu', 'VERB'),
    ];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'CLAUSE');

    // Pick the full-sentence CLAUSE [0-2]
    $clause = collect($trees)->first(fn ($t) => $t['spanStart'] === 0 && $t['spanEnd'] === 2);
    expect($clause)->not->toBeNull();
    expect($clause['constructionName'])->toBe('CLAUSE');
    expect($clause['text'])->toBe('a casa caiu');

    $childNames = collect($clause['children'])->pluck('constructionName');
    expect($childNames)->toContain('SUBJECT');
    expect($childNames)->toContain('PRED');

    $subject = collect($clause['children'])->first(fn ($c) => $c['constructionName'] === 'SUBJECT');
    expect($subject['spanStart'])->toBe(0);
    expect($subject['spanEnd'])->toBe(1);
    expect($subject['text'])->toBe('a casa');

    $pred = collect($clause['children'])->first(fn ($c) => $c['constructionName'] === 'PRED');
    expect($pred['spanStart'])->toBe(2);
    expect($pred['spanEnd'])->toBe(2);
    expect($pred['text'])->toBe('caiu');
});

// --- Children span check: no gaps or overlaps ---

it('ensures children spans tile parent span without gaps or overlaps for CLAUSE', function () {
    $activation = app(SpreadActivationService::class);
    $words = [
        makeDerivWord(0, 'a', 'DET'),
        makeDerivWord(1, 'casa', 'NOUN'),
        makeDerivWord(2, 'caiu', 'VERB'),
    ];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'CLAUSE');

    $clause = collect($trees)->first(fn ($t) => $t['spanStart'] === 0 && $t['spanEnd'] === 2);
    expect($clause)->not->toBeNull();

    $children = $clause['children'];
    expect($children)->not->toBeEmpty();

    // Check children are ordered by spanStart
    for ($i = 1; $i < count($children); $i++) {
        expect($children[$i]['spanStart'])->toBeGreaterThan($children[$i - 1]['spanStart'],
            'Children should be ordered left to right by spanStart');
    }

    // Check children tile the parent span exactly (contiguous)
    $prevEnd = $clause['spanStart'] - 1;
    foreach ($children as $child) {
        expect($child['spanStart'])->toBe($prevEnd + 1,
            "Child {$child['constructionName']} should start right after previous child ends");
        $prevEnd = $child['spanEnd'];
    }
    expect($prevEnd)->toBe($clause['spanEnd'],
        'Last child should end at the CLAUSE span end');
});

// --- Empty input produces no trees ---

it('returns empty trees for empty word list', function () {
    $activation = app(SpreadActivationService::class);
    $result = $activation->activate([], 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, []);

    expect($trees)->toBeEmpty();
});

// --- rootConstructionName filter ---

it('filters derivation trees by rootConstructionName', function () {
    $activation = app(SpreadActivationService::class);
    $words = [makeDerivWord(0, 'casa', 'NOUN')];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);

    $nounTrees = $service->buildDerivationTrees($result, $words, 'NOUN');
    $verbTrees = $service->buildDerivationTrees($result, $words, 'VERB');

    expect($nounTrees)->not->toBeEmpty();
    foreach ($nounTrees as $tree) {
        expect($tree['constructionName'])->toBe('NOUN');
    }

    expect($verbTrees)->toBeEmpty();
});

// --- Tree depth: all nodes have consistent text ---

it('ensures text field matches span words at every level of the tree', function () {
    $activation = app(SpreadActivationService::class);
    $words = [
        makeDerivWord(0, 'a', 'DET'),
        makeDerivWord(1, 'casa', 'NOUN'),
        makeDerivWord(2, 'caiu', 'VERB'),
    ];
    $result = $activation->activate($words, 1);

    $service = app(DerivationService::class);
    $trees = $service->buildDerivationTrees($result, $words, 'CLAUSE');

    $checkNode = null;
    $checkNode = function (array $node) use ($words, &$checkNode): void {
        $expectedText = collect($words)
            ->filter(fn ($w) => $w['position'] >= $node['spanStart'] && $w['position'] <= $node['spanEnd'])
            ->pluck('word')
            ->implode(' ');

        expect($node['text'])->toBe($expectedText,
            "Node {$node['constructionName']} [{$node['spanStart']}-{$node['spanEnd']}] has wrong text");

        foreach ($node['children'] as $child) {
            $checkNode($child);
        }
    };

    foreach ($trees as $tree) {
        $checkNode($tree);
    }
});
