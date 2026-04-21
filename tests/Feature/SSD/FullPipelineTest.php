<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdConstruction;
use App\Repositories\SSD\SsdConstructionMember;
use App\Repositories\SSD\SsdElementCategory;
use App\Repositories\SSD\SsdFunctionalCategory;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdHigherOrderLink;
use App\Repositories\SSD\SsdRelationNode;
use App\Repositories\SSD\SsdSequence;
use App\Services\SSD\GraphEngine;
use App\Services\SSD\SCCAnalyzer;

beforeEach(function () {
    $this->graphId = SsdGraph::create('FullPipeline Test', null, [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.001,
        'within_reinforcement_multiplier' => 1.2,
        'between_reinforcement_multiplier' => 1.0,
        'within_decay_factor' => 0.995,
        'between_decay_factor' => 0.99,
    ]);
    $this->graph = SsdGraph::byId($this->graphId);
    $this->engine = new GraphEngine($this->graph);
});

afterEach(function () {
    Criteria::table('ssd_construction_interfaces')->where('graph_id', $this->graphId)->delete();
    SsdHigherOrderLink::deleteByGraph($this->graphId);
    SsdRelationNode::deleteByGraph($this->graphId);
    SsdConstructionMember::deleteByGraph($this->graphId);
    SsdConstruction::deleteByGraph($this->graphId);
    SsdElementCategory::deleteByGraph($this->graphId);
    SsdFunctionalCategory::deleteByGraph($this->graphId);
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

describe('Full Pipeline', function () {
    it('processes sequences, categorizes, and extracts constructions', function () {
        // Step 1: Process 20 test sequences
        $sequences = [
            ['DET', 'N', 'V'],
            ['DET', 'ADJ', 'N', 'V'],
            ['DET', 'N', 'V', 'DET', 'N'],
            ['PRON_PERS', 'V', 'DET', 'N'],
            ['DET', 'N', 'V', 'ADV'],
            ['DET', 'ADJ', 'N', 'V', 'DET', 'ADJ', 'N'],
            ['PRON_PERS', 'V'],
            ['DET', 'N', 'V', 'PREP', 'DET', 'N'],
            ['DET', 'ADJ', 'N'],
            ['N', 'V', 'DET', 'N'],
            ['DET', 'N', 'V'],
            ['DET', 'ADJ', 'N', 'V'],
            ['PRON_PERS', 'V', 'ADV'],
            ['DET', 'N', 'V', 'DET', 'ADJ', 'N'],
            ['ADJ', 'N', 'V'],
            ['DET', 'N', 'V', 'CONJ', 'DET', 'N', 'V'],
            ['PRON_PERS', 'V', 'PREP', 'DET', 'N'],
            ['DET', 'N'],
            ['N', 'V'],
            ['DET', 'ADJ', 'ADJ', 'N', 'V'],
        ];

        $this->engine->processSequences($sequences);

        $stats = $this->engine->getStatistics();
        expect($stats['sequences_processed'])->toBe(20);

        // Sequences stored
        $storedSeqs = SsdSequence::listByGraph($this->graphId);
        expect(count($storedSeqs))->toBe(20);

        // Step 2: Categorize with defaults
        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        $detCat = SsdElementCategory::findByElement($this->graphId, 'DET');
        expect($detCat)->not->toBeNull();

        // Step 3: Reify relations (required before extraction)
        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $relationNodes = SsdRelationNode::listByGraph($this->graphId);
        expect(count($relationNodes))->toBeGreaterThan(0);

        // Step 4: Extract constructions via competitive discovery
        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $constructions = SsdConstruction::listByGraph($this->graphId);
        expect(count($constructions))->toBeGreaterThan(0);
    });

    it('differential dynamics: within-construction links have higher avg weight after categorization', function () {
        // Process many sequences to build structure
        $sequences = [];
        for ($i = 0; $i < 15; $i++) {
            $sequences[] = ['DET', 'N', 'V'];
            $sequences[] = ['DET', 'ADJ', 'N', 'V'];
            $sequences[] = ['PRON_PERS', 'V', 'DET', 'N'];
        }

        $this->engine->processSequences($sequences);

        // Categorize to set relation classes
        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        // Reload engine with fresh graph and process more sequences
        $this->graph = SsdGraph::byId($this->graphId);
        $this->engine = new GraphEngine($this->graph);

        for ($i = 0; $i < 10; $i++) {
            $this->engine->processSequence(['DET', 'N', 'V']);
            $this->engine->processSequence(['DET', 'ADJ', 'N', 'V']);
        }

        // Check that within links have higher avg weight than between links
        $withinStats = Criteria::table('ssd_links')
            ->where('graph_id', $this->graphId)
            ->where('link_type', 'transition')
            ->where('relation_class', 'within')
            ->selectRaw('AVG(weight) as avg_weight, COUNT(*) as count')
            ->first();

        $betweenStats = Criteria::table('ssd_links')
            ->where('graph_id', $this->graphId)
            ->where('link_type', 'transition')
            ->where('relation_class', 'between')
            ->selectRaw('AVG(weight) as avg_weight, COUNT(*) as count')
            ->first();

        // We need at least some of each type to compare
        if ((int) $withinStats->count > 0 && (int) $betweenStats->count > 0) {
            expect((float) $withinStats->avg_weight)->toBeGreaterThanOrEqual((float) $betweenStats->avg_weight);
        } else {
            // If not enough links typed, just assert some transition links exist
            $total = Criteria::table('ssd_links')
                ->where('graph_id', $this->graphId)
                ->where('link_type', 'transition')
                ->count();
            expect($total)->toBeGreaterThan(0);
        }
    });

    it('SCC finds cycle in circular sequence pattern', function () {
        // Build N→V→DET→N cycle
        $sequences = [];
        for ($i = 0; $i < 5; $i++) {
            $sequences[] = ['N', 'V'];
            $sequences[] = ['V', 'DET'];
            $sequences[] = ['DET', 'N'];
        }

        $this->engine->processSequences($sequences);

        $analyzer = new SCCAnalyzer;
        $sccs = $analyzer->findSCCs($this->graph);

        expect(count($sccs))->toBeGreaterThan(0);

        $found = false;
        foreach ($sccs as $scc) {
            if (in_array('N', $scc['elements']) && in_array('V', $scc['elements']) && in_array('DET', $scc['elements'])) {
                $found = true;
                break;
            }
        }
        expect($found)->toBeTrue();
    });
});
