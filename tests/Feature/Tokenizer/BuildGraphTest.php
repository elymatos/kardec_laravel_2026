<?php

use App\Database\Criteria;
use App\Services\Tokenizer\TokenizerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('TokenizerService::buildGraph()', function () {
    beforeEach(function () {
        $this->service = new TokenizerService;
        // Borrow any existing entity ID required by the lemma table FK
        $this->existingEntityId = Criteria::table('lemma')->value('idEntity');
    });

    it('returns empty array for empty token input', function () {
        $result = $this->service->buildGraph([], 2);

        expect($result)->toBe([]);
    });

    it('returns empty lemmas for a word not in the lexicon', function () {
        $result = $this->service->buildGraph(['zzzneverexistsxxx999'], 2);

        expect($result)->toHaveCount(1);
        expect($result[0]['word'])->toBe('zzzneverexistsxxx999');
        expect($result[0]['lemmas'])->toBe([]);
        expect($result[0]['positions'])->toBe([0]);
    });

    it('resolves a known English word to its L-type lemma node with idLemma', function () {
        // "run" has L node (idLexicon=8370) and lemma idLemma=8369 in language 2 (English)
        $result = $this->service->buildGraph(['run'], 2);

        expect($result)->toHaveCount(1);
        expect($result[0]['word'])->toBe('run');
        expect($result[0]['lemmas'])->not->toBeEmpty();
        expect($result[0]['positions'])->toBe([0]);

        $firstLemma = array_values($result[0]['lemmas'])[0];
        expect($firstLemma['type'])->toBe('L');
        expect($firstLemma)->toHaveKey('idLemma');
        expect($firstLemma['idLemma'])->toBeInt();
    });

    it('excludes lemmas from other languages', function () {
        // "run" only has a lemma for language 2; language 99 should yield empty lemmas
        $result = $this->service->buildGraph(['run'], 99);

        expect($result[0]['lemmas'])->toBe([]);
    });

    it('detects a two-word MWE via AND-gate activation', function () {
        // Insert test lexicon entries with unique forms
        $lex1Id = Criteria::create('lexicon', ['form' => 'testmwe_alpha_word']);
        $lex2Id = Criteria::create('lexicon', ['form' => 'testmwe_beta_word']);
        $lexMId = Criteria::create('lexicon', ['form' => 'testmwe_alpha_word testmwe_beta_word']);

        // Insert lemma rows for both component words (language 1)
        $lemma1Id = Criteria::create('lemma', [
            'idLexicon' => $lex1Id,
            'idLanguage' => 1,
            'idEntity' => $this->existingEntityId,
        ]);
        $lemma2Id = Criteria::create('lemma', [
            'idLexicon' => $lex2Id,
            'idLanguage' => 1,
            'idEntity' => $this->existingEntityId,
        ]);
        $lemmaMId = Criteria::create('lemma', [
            'idLexicon' => $lexMId,
            'idLanguage' => 1,
            'idEntity' => $this->existingEntityId,
        ]);

        // Insert L nodes for the component words and M node for the MWE
        $lNode1Id = Criteria::create('lexicon_node', ['type' => 'L', 'idLexicon' => $lex1Id]);
        $lNode2Id = Criteria::create('lexicon_node', ['type' => 'L', 'idLexicon' => $lex2Id]);
        $mNodeId = Criteria::create('lexicon_node', ['type' => 'M', 'idLexicon' => $lexMId]);

        // Insert A (AND-gate) node
        $aNodeId = Criteria::create('lexicon_node', ['type' => 'A', 'idLexicon' => null]);

        // Wire: L1 → A, L2 → A, A → M
        Criteria::table('lexicon_link')->insert(['idLexiconNodeSource' => $lNode1Id, 'idLexiconNodeTarget' => $aNodeId]);
        Criteria::table('lexicon_link')->insert(['idLexiconNodeSource' => $lNode2Id, 'idLexiconNodeTarget' => $aNodeId]);
        Criteria::table('lexicon_link')->insert(['idLexiconNodeSource' => $aNodeId, 'idLexiconNodeTarget' => $mNodeId]);

        $result = $this->service->buildGraph(['testmwe_alpha_word', 'testmwe_beta_word'], 1);

        // 2 single-word entries + 1 MWE entry
        expect($result)->toHaveCount(3);

        // Single-word entries have only L-type lemmas (no M nodes leak into token positions)
        expect($result[0]['lemmas'])->toHaveKey($lNode1Id);
        expect($result[0]['lemmas'][$lNode1Id]['type'])->toBe('L');
        expect($result[0]['lemmas'][$lNode1Id]['idLemma'])->toBe($lemma1Id);
        expect($result[0]['positions'])->toBe([0]);
        expect(array_filter($result[0]['lemmas'], fn (array $l) => $l['type'] === 'M'))->toBe([]);

        expect($result[1]['lemmas'])->toHaveKey($lNode2Id);
        expect($result[1]['lemmas'][$lNode2Id]['type'])->toBe('L');
        expect($result[1]['lemmas'][$lNode2Id]['idLemma'])->toBe($lemma2Id);
        expect($result[1]['positions'])->toBe([1]);
        expect(array_filter($result[1]['lemmas'], fn (array $l) => $l['type'] === 'M'))->toBe([]);

        // MWE entry has M-type lemma with idLemma and correct positions
        $mweEntry = collect($result)->first(fn (array $e): bool => count($e['positions'] ?? []) > 1);
        expect($mweEntry)->not->toBeNull();
        expect($mweEntry['word'])->toBe('testmwe_alpha_word testmwe_beta_word');
        expect($mweEntry['lemmas'][$mNodeId]['type'])->toBe('M');
        expect($mweEntry['lemmas'][$mNodeId]['idLemma'])->toBe($lemmaMId);
        expect($mweEntry['positions'])->toContain(0);
        expect($mweEntry['positions'])->toContain(1);
    });
});

describe('TokenizerService::setStartEnd()', function () {
    beforeEach(function () {
        $this->service = new TokenizerService;
        $this->call = function (string $text, array $tokens): array {
            $ref = new ReflectionMethod($this->service, 'setStartEnd');
            $ref->setAccessible(true);

            return $ref->invoke($this->service, $text, $tokens);
        };
    });

    it('attaches startChar and endChar to each single-token entry', function () {
        $tokens = [
            ['word' => 'hello', 'lemmas' => [], 'positions' => [0]],
            ['word' => 'world', 'lemmas' => [], 'positions' => [1]],
        ];

        $result = ($this->call)('Hello World', $tokens);

        expect($result[0]['startChar'])->toBe(0);
        expect($result[0]['endChar'])->toBe(4);   // H-e-l-l-o (0–4)
        expect($result[1]['startChar'])->toBe(6);
        expect($result[1]['endChar'])->toBe(10);  // W-o-r-l-d (6–10)
    });

    it('derives MWE startChar/endChar from its component positions', function () {
        $tokens = [
            ['word' => 'hello', 'lemmas' => [], 'positions' => [0]],
            ['word' => 'world', 'lemmas' => [], 'positions' => [1]],
            ['word' => 'hello world', 'lemmas' => [], 'positions' => [0, 1]],
        ];

        $result = ($this->call)('Hello World', $tokens);

        expect($result[2]['startChar'])->toBe(0);
        expect($result[2]['endChar'])->toBe(10);
    });

    it('handles multibyte characters correctly', function () {
        $tokens = [
            ['word' => 'saúde', 'lemmas' => [], 'positions' => [0]],
            ['word' => 'mental', 'lemmas' => [], 'positions' => [1]],
        ];

        $result = ($this->call)('Saúde Mental', $tokens);

        expect($result[0]['startChar'])->toBe(0);
        expect($result[0]['endChar'])->toBe(4);  // S-a-ú-d-e = 5 chars, indices 0–4
        expect($result[1]['startChar'])->toBe(6);
        expect($result[1]['endChar'])->toBe(11); // M-e-n-t-a-l = 6 chars, indices 6–11
    });

    it('sets startChar and endChar to null when a word cannot be found', function () {
        $tokens = [
            ['word' => 'missing', 'lemmas' => [], 'positions' => [0]],
        ];

        $result = ($this->call)('completely different text', $tokens);

        expect($result[0]['startChar'])->toBeNull();
        expect($result[0]['endChar'])->toBeNull();
    });
});
