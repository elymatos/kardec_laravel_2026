<?php

use App\Data\Parser\ActivationToken;

beforeEach(function () {
    ActivationToken::resetIdCounter();
});

it('allows contiguous spans (next starts right after current ends)', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 1,
        endWordPosition: 1,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 2,
        endWordPosition: 2,
    );

    expect($intra->isSpanCompatible($inter))->toBeTrue();
});

it('rejects spans with gaps (REL bug: PRX@1 should not match CLAUSE@3)', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 1,
        endWordPosition: 1,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 3,
        endWordPosition: 3,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});

it('rejects overlapping spans (SUBJECT@[0,1] followed by PRED@[1,1])', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 0,
        endWordPosition: 1,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 1,
        endWordPosition: 1,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});

it('allows position-independent tokens (bypass/START)', function () {
    $posIndependent = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 0,
    );
    $positioned = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 5,
        endWordPosition: 5,
    );

    expect($posIndependent->isSpanCompatible($positioned))->toBeTrue();
    expect($positioned->isSpanCompatible($posIndependent))->toBeTrue();
});

it('rejects backwards spans (other starts before this)', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 2,
        endWordPosition: 3,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 1,
        endWordPosition: 2,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});

it('allows multi-word contiguous spans (REF@[0,2] + PRED@[3,3])', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 0,
        endWordPosition: 2,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 3,
        endWordPosition: 3,
    );

    expect($intra->isSpanCompatible($inter))->toBeTrue();
});

it('rejects multi-word spans with gaps (REF@[0,1] + PRED@[3,3])', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 0,
        endWordPosition: 1,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 3,
        endWordPosition: 3,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});

it('rejects overlapping recursive composition (PRX@[1,1] + CLAUSE@[1-3])', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 1,
        endWordPosition: 1,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 1,
        endWordPosition: 3,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});

it('allows strict contiguity with expectedNextPosition', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 0,
        endWordPosition: 2,
        expectedNextPosition: 3,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 3,
        endWordPosition: 5,
    );

    expect($intra->isSpanCompatible($inter))->toBeTrue();
});

it('rejects wrong position with expectedNextPosition', function () {
    $intra = ActivationToken::create(
        sourceGrammarNodeId: 1,
        sourceParserNodeId: 1,
        startWordPosition: 0,
        endWordPosition: 2,
        expectedNextPosition: 3,
    );
    $inter = ActivationToken::create(
        sourceGrammarNodeId: 2,
        sourceParserNodeId: 2,
        startWordPosition: 2,
        endWordPosition: 4,
    );

    expect($intra->isSpanCompatible($inter))->toBeFalse();
});
