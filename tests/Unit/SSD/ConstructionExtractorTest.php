<?php

use App\Services\SSD\ConstructionExtractor;
use App\Services\SSD\LouvainCommunityDetector;

/**
 * Helper: invoke the private computePpmiEdges method via Reflection.
 * setAccessible() has had no effect since PHP 8.1 and is omitted here.
 *
 * @param  array<array{source: string, target: string, weight: float}>  $edges
 * @return array<array{source: string, target: string, weight: float, raw_weight: float}>
 */
function callComputePpmiEdges(array $edges): array
{
    $extractor = new ConstructionExtractor(new LouvainCommunityDetector);
    $method = new ReflectionMethod($extractor, 'computePpmiEdges');

    return $method->invoke($extractor, $edges);
}

describe('ConstructionExtractor::computePpmiEdges', function () {
    it('returns empty array unchanged', function () {
        expect(callComputePpmiEdges([]))->toBe([]);
    });

    it('returns raw edges when total weight is zero', function () {
        $edges = [['source' => 'A', 'target' => 'B', 'weight' => 0.0]];
        expect(callComputePpmiEdges($edges))->toBe($edges);
    });

    it('computes positive PPMI for an exclusive pair', function () {
        // A→B: exclusive co-occurrence (A and B only appear together).
        // C→D, C→E, C→F: C spreads its mass across three targets.
        // PPMI(A→B) should exceed PPMI(C→D) even though raw weights are equal.
        //
        // Manual verification (totalWeight=20, twoM=40):
        //   A→B: pAB=0.25, pA=0.125, pB=0.125 → PMI = log2(0.25/0.015625) = log2(16) = 4.0
        //   C→D: pAB=0.25, pC=0.375, pD=0.125 → PMI = log2(0.25/0.046875) ≈ 2.41
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 5.0],
            ['source' => 'C', 'target' => 'D', 'weight' => 5.0],
            ['source' => 'C', 'target' => 'E', 'weight' => 5.0],
            ['source' => 'C', 'target' => 'F', 'weight' => 5.0],
        ];

        $result = callComputePpmiEdges($edges);

        $ppmiAB = array_values(array_filter($result, fn ($e) => $e['source'] === 'A' && $e['target'] === 'B'))[0]['weight'];
        $ppmiCD = array_values(array_filter($result, fn ($e) => $e['source'] === 'C' && $e['target'] === 'D'))[0]['weight'];

        expect($ppmiAB)->toBeGreaterThan($ppmiCD);
        expect($ppmiAB)->toBeGreaterThan(3.9)->and($ppmiAB)->toBeLessThan(4.1);
    });

    it('drops anti-associations (negative PMI → PPMI = 0)', function () {
        // A→B co-occurs rarely despite A and B both being high-frequency hubs.
        // A mostly appears with C (100×), B mostly appears with D (100×).
        // Their mutual presence is far less than expected from their frequencies.
        //
        // Manual verification (totalWeight=201, twoM=402):
        //   A→B: pAB=1/201, pA=101/402, pB=101/402
        //   PMI = log2((1/201) / ((101/402)²)) = log2((402²)/(201·101²)) ≈ -3.67  → PPMI = 0 → dropped
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 1.0],
            ['source' => 'A', 'target' => 'C', 'weight' => 100.0],
            ['source' => 'D', 'target' => 'B', 'weight' => 100.0],
        ];

        $result = callComputePpmiEdges($edges);

        // A→B has negative PMI and must be absent from the result
        $abEdges = array_values(array_filter($result, fn ($e) => $e['source'] === 'A' && $e['target'] === 'B'));
        expect($abEdges)->toBeEmpty();

        // A→C and D→B have positive PMI and must be present
        $acEdges = array_values(array_filter($result, fn ($e) => $e['source'] === 'A' && $e['target'] === 'C'));
        expect($acEdges)->not->toBeEmpty();
        expect($acEdges[0]['weight'])->toBeGreaterThan(0.0);
    });

    it('preserves raw_weight alongside the PPMI weight', function () {
        $edges = [['source' => 'A', 'target' => 'B', 'weight' => 5.0]];

        $result = callComputePpmiEdges($edges);

        expect($result[0])->toHaveKey('raw_weight')
            ->and($result[0]['raw_weight'])->toBe(5.0);
    });

    it('falls back to raw edges when total weight is zero', function () {
        $edges = [['source' => 'X', 'target' => 'Y', 'weight' => 0.0]];
        $result = callComputePpmiEdges($edges);
        expect($result)->toBe($edges);
    });

    it('PPMI reverses hub-node dominance: tight structural pair outranks frequent hubs', function () {
        // Linguistic scenario reflecting the real data:
        //   NOUN↔ADP: raw 50, but both are hubs connecting to many elements
        //   NUM↔SYM:  raw 5,  but NUM and SYM almost exclusively co-occur
        //
        // Expected outcome: PPMI(NUM→SYM) >> PPMI(NOUN→ADP)
        //
        // Manual verification (totalWeight=115, twoM=230):
        //   NUM→SYM: pAB=5/115, pNUM=5/230, pSYM=5/230
        //            PMI = log2((5/115)/((5/230)²)) = log2((5·230²)/(115·25)) ≈ 6.52
        //   NOUN→ADP: pAB=50/115, pNOUN=80/230, pADP=80/230
        //             PMI = log2((50/115)/((80/230)²)) ≈ 1.85
        $edges = [
            ['source' => 'NOUN', 'target' => 'ADP',  'weight' => 50.0],
            ['source' => 'NOUN', 'target' => 'DET',  'weight' => 30.0],
            ['source' => 'ADP',  'target' => 'PRON', 'weight' => 30.0],
            ['source' => 'NUM',  'target' => 'SYM',  'weight' => 5.0],
        ];

        $result = callComputePpmiEdges($edges);

        $ppmiNounAdp = array_values(array_filter($result, fn ($e) => $e['source'] === 'NOUN' && $e['target'] === 'ADP'))[0]['weight'];
        $ppmiNumSym = array_values(array_filter($result, fn ($e) => $e['source'] === 'NUM' && $e['target'] === 'SYM'))[0]['weight'];

        // Despite lower raw weight, NUM→SYM dominates in PPMI
        expect($ppmiNumSym)->toBeGreaterThan($ppmiNounAdp);
        expect($ppmiNumSym)->toBeGreaterThan(5.0);  // large positive PMI
        expect($ppmiNounAdp)->toBeGreaterThan(0.0); // still positive, just not dominant
    });
});
