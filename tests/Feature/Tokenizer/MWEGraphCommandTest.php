<?php

use App\Database\Criteria;

describe('MWEGraphCommand', function () {
    it('displays help information when called with --help', function () {
        $this->artisan('tokenizer:mwe-graph', ['--help' => true])
            ->assertSuccessful();
    });

    it('runs in dry-run mode without modifying node or link counts', function () {
        $initialNodeCount = Criteria::table('lexicon_mwe_node')->count();
        $initialLinkCount = Criteria::table('lexicon_mwe_link')->count();

        $this->artisan('tokenizer:mwe-graph', ['--dry-run' => true])
            ->assertSuccessful();

        expect(Criteria::table('lexicon_mwe_node')->count())->toBe($initialNodeCount);
        expect(Criteria::table('lexicon_mwe_link')->count())->toBe($initialLinkCount);
    });

    it('outputs metric labels in dry-run mode', function () {
        $this->artisan('tokenizer:mwe-graph', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('MWEs processed')
            ->expectsOutputToContain('M nodes created')
            ->expectsOutputToContain('C nodes created')
            ->expectsOutputToContain('A nodes created')
            ->expectsOutputToContain('Links created');
    });

    it('has source data in lexicon_mwe', function () {
        $count = Criteria::table('lexicon_mwe')->count();
        expect($count)->toBeGreaterThan(0);
    });

    describe('real build', function () {
        beforeEach(function () {
            Criteria::table('lexicon_mwe_link')->delete();
            Criteria::table('lexicon_mwe_node')->delete();
        });

        it('creates one M node per distinct MWE lemma', function () {
            $expectedMCount = Criteria::table('lexicon_mwe')
                ->distinct()
                ->count('idLemma');

            $this->artisan('tokenizer:mwe-graph')
                ->assertSuccessful();

            $actualMCount = Criteria::table('lexicon_mwe_node')
                ->where('type', '=', 'M')
                ->count();

            expect($actualMCount)->toBe($expectedMCount);
        });

        it('creates one C node per distinct component lemma', function () {
            $expectedCCount = Criteria::table('lexicon_mwe')
                ->distinct()
                ->count('idLemmaComponent');

            $this->artisan('tokenizer:mwe-graph')
                ->assertSuccessful();

            $actualCCount = Criteria::table('lexicon_mwe_node')
                ->where('type', '=', 'C')
                ->count();

            expect($actualCCount)->toBe($expectedCCount);
        });

        it('creates A nodes and links', function () {
            $this->artisan('tokenizer:mwe-graph')
                ->assertSuccessful();

            $aNodeCount = Criteria::table('lexicon_mwe_node')
                ->where('type', '=', 'A')
                ->count();

            $linkCount = Criteria::table('lexicon_mwe_link')->count();

            expect($aNodeCount)->toBeGreaterThan(0);
            expect($linkCount)->toBeGreaterThan(0);
        });

        it('rebuild clears and recreates the graph identically on a second run', function () {
            // First build
            $this->artisan('tokenizer:mwe-graph', ['--rebuild' => true])
                ->assertSuccessful();

            $nodeCountFirst = Criteria::table('lexicon_mwe_node')->count();
            $linkCountFirst = Criteria::table('lexicon_mwe_link')->count();

            expect($nodeCountFirst)->toBeGreaterThan(0);
            expect($linkCountFirst)->toBeGreaterThan(0);

            // Second build with --rebuild
            $this->artisan('tokenizer:mwe-graph', ['--rebuild' => true])
                ->assertSuccessful();

            $nodeCountSecond = Criteria::table('lexicon_mwe_node')->count();
            $linkCountSecond = Criteria::table('lexicon_mwe_link')->count();

            expect($nodeCountSecond)->toBe($nodeCountFirst);
            expect($linkCountSecond)->toBe($linkCountFirst);
        });
    });
});
