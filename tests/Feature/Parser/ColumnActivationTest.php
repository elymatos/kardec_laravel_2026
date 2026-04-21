<?php

use App\Services\Parser\ColumnActivationService;
use Illuminate\Support\Facades\DB;

/**
 * Helper to build a word array matching InputParserService format.
 */
function makeColumnWord(int $position, string $word, string $pos, string $lemma = '', string $deprel = 'root'): array
{
    return [
        'position' => $position,
        'word' => $word,
        'lemma' => $lemma ?: $word,
        'pos' => $pos,
        'deprel' => $deprel,
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

// --- Single Word Column Tests ---

it('completes NOUN and REF for single word "casa"', function () {
    $service = app(ColumnActivationService::class);
    $words = [makeColumnWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('REF');

    // REF should fire at [0,0] via INTER_PATTERN within the same column
    $refCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'REF');
    expect($refCompletion['spanStart'])->toBe(0);
    expect($refCompletion['spanEnd'])->toBe(0);
});

// --- Two-Word Column Isolation Tests ---

it('produces DET+REF in column 0 and NOUN+REF in column 1 but no multi-word REF for "a casa"', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
    ];

    $result = $service->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    expect($completedNames)->toContain('DET');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('REF');

    // No REF spanning both words [0,1]
    $multiWordRef = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'REF' && $c['spanStart'] === 0 && $c['spanEnd'] === 1);
    expect($multiWordRef)->toBeNull();
});

// --- All Constructions Span Single Word ---

it('ensures all constructions span a single word', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
    ];

    $result = $service->activate($words, 1);

    foreach ($result['completedConstructions'] as $completion) {
        expect($completion['spanStart'])->toBe($completion['spanEnd'],
            "Construction {$completion['constructionName']} spans [{$completion['spanStart']},{$completion['spanEnd']}] but should be single-word"
        );
    }
});

// --- No Multi-Word Spans ---

it('produces no multi-word spans', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
        makeColumnWord(2, 'caiu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    $multiWordSpans = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['spanStart'] !== $c['spanEnd']);
    expect($multiWordSpans)->toBeEmpty('No construction should span more than one word position');
});

// --- Globally Unique Parser Node IDs ---

it('assigns globally unique parser node IDs across all columns', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
    ];

    $result = $service->activate($words, 1);

    $ids = collect($result['nodes'])->pluck('id')->toArray();
    $uniqueIds = array_unique($ids);
    expect(count($ids))->toBe(count($uniqueIds), 'All parser node IDs should be unique');
});

// --- Empty Word List ---

it('handles empty word list with no completions and 0 columns', function () {
    $service = app(ColumnActivationService::class);

    $result = $service->activate([], 1);

    expect($result['completedConstructions'])->toBeEmpty();
    expect($result['statistics']['columns'])->toBe(0);
    expect($result['statistics']['words_processed'])->toBe(0);
});

// --- Statistics ---

it('returns statistics with columns key matching word count', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
        makeColumnWord(2, 'caiu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    expect($result['statistics'])->toHaveKeys([
        'total_nodes',
        'total_edges',
        'words_processed',
        'activation_rounds',
        'completed_constructions',
        'columns',
    ]);
    expect($result['statistics']['columns'])->toBe(3);
    expect($result['statistics']['words_processed'])->toBe(3);
});

// --- LITERAL Matching ---

it('completes PRX for "que" via LITERAL node', function () {
    $service = app(ColumnActivationService::class);
    $words = [makeColumnWord(0, 'que', 'SCONJ')];

    $result = $service->activate($words, 1);

    $prxCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'PRX');
    expect($prxCompletion)->not->toBeNull();
    expect($prxCompletion['spanStart'])->toBe(0);
    expect($prxCompletion['spanEnd'])->toBe(0);
});

// --- Multi-Word Sentence ---

it('produces separate column constructions for "a casa caiu" with no cross-column spans', function () {
    $service = app(ColumnActivationService::class);
    $words = [
        makeColumnWord(0, 'a', 'DET'),
        makeColumnWord(1, 'casa', 'NOUN'),
        makeColumnWord(2, 'caiu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    expect($completedNames)->toContain('DET');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('VERB');
    expect($completedNames)->toContain('REF');

    // No cross-column constructions
    $crossColumnCompletions = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['spanStart'] !== $c['spanEnd']);
    expect($crossColumnCompletions)->toBeEmpty('No construction should span multiple word positions');

    // SUBJECT = {CXN:REF}, so it can complete from a single-word REF.
    // The column parser does not enforce cross-column constraints; verify
    // that any SUBJECT completion is single-position (not cross-column).
    $subjectCompletions = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['constructionName'] === 'SUBJECT');
    foreach ($subjectCompletions as $s) {
        expect($s['spanStart'])->toEqual($s['spanEnd'], 'SUBJECT should not span multiple columns');
    }
});
