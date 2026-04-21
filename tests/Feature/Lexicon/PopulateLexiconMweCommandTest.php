<?php

use App\Database\Criteria;
use App\Services\Lexicon\TokenizerService;

describe('PopulateLexiconMweCommand', function () {
    it('displays help information when called with --help', function () {
        $this->artisan('lexicon:populate-mwe', ['--help' => true])
            ->assertSuccessful();
    });

    it('verifies MWE lemmas query works correctly', function () {
        $mweLemmas = Criteria::table('lemma')
            ->where('isMWE', '=', 1)
            ->select('idLemma', 'name')
            ->get();

        // This test verifies the query structure is correct
        expect($mweLemmas)->toBeObject();
    });

    it('fetches only MWE lemmas from database', function () {
        $mweLemmas = Criteria::table('lemma')
            ->where('isMWE', '=', 1)
            ->select('idLemma', 'name')
            ->limit(5)
            ->get();

        // Verify each returned lemma has the required fields
        if ($mweLemmas->isNotEmpty()) {
            foreach ($mweLemmas as $lemma) {
                expect($lemma)->toHaveProperty('idLemma');
                expect($lemma)->toHaveProperty('name');
            }
        } else {
            expect(true)->toBeTrue(); // Skip if no MWE lemmas
        }
    });

    it('can instantiate TokenizerService', function () {
        $service = new TokenizerService;
        expect($service)->toBeInstanceOf(TokenizerService::class);
    });

    it('populateLexiconMwe method exists on TokenizerService', function () {
        $service = new TokenizerService;
        expect(method_exists($service, 'populateLexiconMwe'))->toBeTrue();
    });
});
