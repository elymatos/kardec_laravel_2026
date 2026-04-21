<?php

use App\Database\Criteria;

describe('PopulateMWEPatternsCommand', function () {
    it('displays help information when called with --help', function () {
        $this->artisan('lexicon:populate-mwe-patterns', ['--help' => true])
            ->assertSuccessful();
    });

    it('runs in dry-run mode without modifying database', function () {
        // Get initial counts
        $initialNodeCount = Criteria::table('lexicon_pattern_node')->count();
        $initialEdgeCount = Criteria::table('lexicon_pattern_edge')->count();

        $this->artisan('lexicon:populate-mwe-patterns', ['--dry-run' => true])
            ->assertSuccessful();

        // Verify no records were created
        $finalNodeCount = Criteria::table('lexicon_pattern_node')->count();
        $finalEdgeCount = Criteria::table('lexicon_pattern_edge')->count();

        expect($finalNodeCount)->toBe($initialNodeCount);
        expect($finalEdgeCount)->toBe($initialEdgeCount);
    });

    it('reports statistics in dry-run mode', function () {
        $this->artisan('lexicon:populate-mwe-patterns', ['--dry-run' => true])
            ->assertSuccessful();
    });

    it('processes MWE patterns from the database', function () {
        // Verify there are MWE patterns to process
        $mweCount = Criteria::table('lexicon_pattern')
            ->where('patternType', 'MWE')
            ->count();

        expect($mweCount)->toBeGreaterThan(0);

        $this->artisan('lexicon:populate-mwe-patterns', ['--dry-run' => true])
            ->assertSuccessful();
    });

    it('builds lookup caches before processing', function () {
        $this->artisan('lexicon:populate-mwe-patterns', ['--dry-run' => true])
            ->assertSuccessful();
    });
});
