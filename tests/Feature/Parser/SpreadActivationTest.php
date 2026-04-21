<?php

use App\Services\Parser\SpreadActivationService;
use Illuminate\Support\Facades\DB;

/**
 * Helper to build a word array matching InputParserService format.
 */
function makeWord(int $position, string $word, string $pos, string $lemma = '', string $deprel = 'root'): array
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
    // Ensure grammar graph is built for grammar 1
    $nodeCount = DB::table('parser_pattern_node')->count();

    if ($nodeCount === 0) {
        $this->artisan('parser:build-grammar-graph --grammar=1');
    }
});

// --- Basic Seeding Tests ---

it('seeds SLOT nodes by POS matching', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    // Should have at least one SLOT node for NOUN
    $slotNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'SLOT');
    expect($slotNodes)->not->toBeEmpty();

    // Slot should have span [0,0]
    $firstSlot = $slotNodes->first();
    expect($firstSlot->spanStart)->toBe(0);
    expect($firstSlot->spanEnd)->toBe(0);
    expect($firstSlot->matchedFeatures['word'])->toBe('casa');
});

it('seeds LITERAL nodes by word form matching', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'que', 'SCONJ')];

    $result = $service->activate($words, 1);

    // Should have a LITERAL node for "que"
    $literalNodes = collect($result['nodes'])->filter(
        fn ($n) => $n->type === 'LITERAL' && ($n->properties['value'] ?? '') === 'que'
    );
    expect($literalNodes)->not->toBeEmpty();
});

it('does not seed SLOT nodes for non-matching POS', function () {
    $service = app(SpreadActivationService::class);
    // Use a POS that doesn't exist in the grammar
    $words = [makeWord(0, 'xyz', 'INTJ')];

    $result = $service->activate($words, 1);

    $slotNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'SLOT');
    expect($slotNodes)->toBeEmpty();
});

// --- Simple Construction Completion ---

it('completes a simple NOUN construction from a NOUN word', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    // Should have at least one END node that fired
    $endNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'END');
    expect($endNodes)->not->toBeEmpty();

    // NOUN construction should be completed
    $nounCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'NOUN');
    expect($nounCompletion)->not->toBeNull();
    expect($nounCompletion['spanStart'])->toBe(0);
    expect($nounCompletion['spanEnd'])->toBe(0);
});

it('completes a VERB construction from a VERB word', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'comeu', 'VERB')];

    $result = $service->activate($words, 1);

    $verbCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'VERB');
    expect($verbCompletion)->not->toBeNull();
    expect($verbCompletion['spanStart'])->toBe(0);
    expect($verbCompletion['spanEnd'])->toBe(0);
});

it('completes NOUN construction for PROPN word (alternative branch)', function () {
    $service = app(SpreadActivationService::class);
    // NOUN construction has START -> PROPN slot -> END as alternative to NOUN
    $words = [makeWord(0, 'João', 'PROPN')];

    $result = $service->activate($words, 1);

    $nounCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'NOUN');
    expect($nounCompletion)->not->toBeNull();
});

// --- CXN_REF Join Tests ---

it('completes REF construction with NOUN word via CXN_REF chain', function () {
    $service = app(SpreadActivationService::class);
    // REF pattern: START -> [DET?] -> [ADJ?] -> NOUN_REF -> [ADJ?] -> END
    // With bypass edges, NOUN alone should complete REF
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    $refCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'REF');
    expect($refCompletion)->not->toBeNull();
    expect($refCompletion['spanStart'])->toBe(0);
    expect($refCompletion['spanEnd'])->toBe(0);
});

it('completes REF construction with DET + NOUN words', function () {
    $service = app(SpreadActivationService::class);
    $words = [
        makeWord(0, 'a', 'DET'),
        makeWord(1, 'casa', 'NOUN'),
    ];

    $result = $service->activate($words, 1);

    // Should have both DET and REF constructions completed
    $detCompletion = collect($result['completedConstructions'])
        ->first(fn ($c) => $c['constructionName'] === 'DET');
    expect($detCompletion)->not->toBeNull();

    $refCompletions = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['constructionName'] === 'REF');
    expect($refCompletions)->not->toBeEmpty();

    // At least one REF should span [0,1] (DET + NOUN)
    $fullRef = $refCompletions->first(fn ($c) => $c['spanStart'] === 0 && $c['spanEnd'] === 1);
    expect($fullRef)->not->toBeNull();
});

// --- Bypass/INTERMEDIATE Chain Tests ---

it('propagates bypass edges through INTERMEDIATE nodes', function () {
    $service = app(SpreadActivationService::class);
    // REF has bypass edges: START -> INTERMEDIATE (bypassing DET)
    // NOUN alone should still complete REF through bypass chain
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    // Should have INTERMEDIATE nodes in the parser graph (from bypass)
    $intermediateNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'INTERMEDIATE');
    expect($intermediateNodes)->not->toBeEmpty();
});

it('bypass-only tokens do not complete END nodes', function () {
    $service = app(SpreadActivationService::class);
    // Use a word that doesn't match any construction but might create bypass tokens
    // An INTJ won't match any SLOT, so no END should fire
    $words = [makeWord(0, 'uau', 'INTJ')];

    $result = $service->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    // No constructions should complete from a bare INTJ
    expect($completedNames)->not->toContain('REF');
});

// --- Multi-Word Sentence Tests ---

it('handles multi-word sentence with independent constructions', function () {
    $service = app(SpreadActivationService::class);
    $words = [
        makeWord(0, 'João', 'PROPN'),
        makeWord(1, 'comeu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    // Both NOUN and VERB constructions should complete
    $completedNames = collect($result['completedConstructions'])->pluck('constructionName');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('VERB');
});

it('handles DET + NOUN + VERB sentence', function () {
    $service = app(SpreadActivationService::class);
    $words = [
        makeWord(0, 'a', 'DET'),
        makeWord(1, 'casa', 'NOUN'),
        makeWord(2, 'caiu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    $completedNames = collect($result['completedConstructions'])->pluck('constructionName')->toArray();
    expect($completedNames)->toContain('DET');
    expect($completedNames)->toContain('NOUN');
    expect($completedNames)->toContain('VERB');
    expect($completedNames)->toContain('REF');
});

// --- Statistics Tests ---

it('returns correct statistics', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    expect($result['statistics'])->toHaveKeys([
        'total_nodes',
        'total_edges',
        'words_processed',
        'activation_rounds',
        'completed_constructions',
    ]);
    expect($result['statistics']['words_processed'])->toBe(1);
    expect($result['statistics']['total_nodes'])->toBeGreaterThan(0);
    expect($result['statistics']['activation_rounds'])->toBeGreaterThan(0);
});

// --- Overlap Rejection Tests ---

it('rejects overlapping recursive REL construction', function () {
    $service = app(SpreadActivationService::class);
    // Sentence: "casa que João comeu" — if CLAUSE forms at [1-3] overlapping PRX@[1,1],
    // a spurious REL would appear. Strict contiguity should prevent this.
    $words = [
        makeWord(0, 'casa', 'NOUN'),
        makeWord(1, 'que', 'SCONJ'),
        makeWord(2, 'João', 'PROPN'),
        makeWord(3, 'comeu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    // REL constructions should only have valid contiguous spans (no overlaps).
    // PRX fires at [1,1], so CLAUSE must start at 2. Any REL starting at 1
    // must span at least [1,2+]. A REL@[1,1] would indicate overlap.
    $relCompletions = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['constructionName'] === 'REL');

    // No REL should exist where the span suggests CLAUSE overlaps PRX.
    // Specifically, no REL with spanStart == spanEnd (single-position, impossible for 2-element pattern).
    $spuriousRels = $relCompletions->filter(fn ($c) => $c['spanStart'] === $c['spanEnd']);
    expect($spuriousRels)->toBeEmpty('No single-position REL should exist (would indicate overlap)');

    // Any valid REL must have CLAUSE starting after PRX ends
    foreach ($relCompletions as $rel) {
        expect($rel['spanEnd'])->toBeGreaterThan($rel['spanStart']);
    }
});

it('allows valid REL with contiguous PRX and CLAUSE', function () {
    $service = app(SpreadActivationService::class);
    // Sentence: "casa que comeu" — PRX@[1,1] + CLAUSE@[2,2] (just PRED) → REL@[1,2]
    $words = [
        makeWord(0, 'casa', 'NOUN'),
        makeWord(1, 'que', 'SCONJ'),
        makeWord(2, 'comeu', 'VERB'),
    ];

    $result = $service->activate($words, 1);

    $relCompletions = collect($result['completedConstructions'])
        ->filter(fn ($c) => $c['constructionName'] === 'REL');

    // Should have at least one valid REL@[1,2]
    $validRel = $relCompletions->first(fn ($c) => $c['spanStart'] === 1 && $c['spanEnd'] === 2);
    expect($validRel)->not->toBeNull();
});

// --- Edge Cases ---

it('handles empty word list', function () {
    $service = app(SpreadActivationService::class);

    $result = $service->activate([], 1);

    expect($result['completedConstructions'])->toBeEmpty();
    // Should still have some nodes (START emits tokens)
    expect($result['statistics']['words_processed'])->toBe(0);
});

it('creates parser graph edges between activated nodes', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    expect($result['edges'])->not->toBeEmpty();

    // All edges should have valid parser node IDs
    $nodeIds = collect($result['nodes'])->pluck('id')->toArray();

    foreach ($result['edges'] as $edge) {
        expect($nodeIds)->toContain($edge->fromParserNodeId);
        expect($nodeIds)->toContain($edge->toParserNodeId);
    }
});

it('records activation round on parser nodes', function () {
    $service = app(SpreadActivationService::class);
    $words = [makeWord(0, 'casa', 'NOUN')];

    $result = $service->activate($words, 1);

    // Seed nodes should be round 0
    $slotNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'SLOT');

    foreach ($slotNodes as $node) {
        expect($node->activationRound)->toBe(0);
    }

    // END nodes should be at a later round
    $endNodes = collect($result['nodes'])->filter(fn ($n) => $n->type === 'END');

    foreach ($endNodes as $node) {
        expect($node->activationRound)->toBeGreaterThanOrEqual(1);
    }
});
