<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdSequence;
use App\Services\SSD\GraphEngine;
use App\Services\SSD\SCCAnalyzer;

beforeEach(function () {
    $this->graphId = SsdGraph::create('SCC Test Graph', null, [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.0,
    ]);
    $this->graph = SsdGraph::byId($this->graphId);
    $this->engine = new GraphEngine($this->graph);
    $this->analyzer = new SCCAnalyzer;
});

afterEach(function () {
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

describe('SCCAnalyzer', function () {
    it('finds no SCCs in acyclic graph', function () {
        $this->engine->processSequences([
            ['DET', 'N', 'V'],
        ]);

        $sccs = $this->analyzer->findSCCs($this->graph);

        // DET→N→V is acyclic, no SCC with 2+ elements
        expect($sccs)->toBe([]);
    });

    it('finds SCC in a cyclic sequence pattern', function () {
        // Create a cycle N→V→DET→N by processing both forward and backward
        $this->engine->processSequences([
            ['N', 'V'],
            ['V', 'DET'],
            ['DET', 'N'],
            // Repeat several times to build strong links
            ['N', 'V'],
            ['V', 'DET'],
            ['DET', 'N'],
            ['N', 'V'],
            ['V', 'DET'],
            ['DET', 'N'],
        ]);

        $sccs = $this->analyzer->findSCCs($this->graph);

        expect(count($sccs))->toBeGreaterThan(0);

        // Find the SCC containing N, V, DET
        $bigScc = null;
        foreach ($sccs as $scc) {
            if (in_array('N', $scc['elements']) && in_array('V', $scc['elements'])) {
                $bigScc = $scc;
                break;
            }
        }

        expect($bigScc)->not->toBeNull();
        expect($bigScc['size'])->toBe(3);
        expect($bigScc['elements'])->toContain('N')
            ->and($bigScc['elements'])->toContain('V')
            ->and($bigScc['elements'])->toContain('DET');
    });

    it('findCycleThrough returns a cycle when one exists', function () {
        $this->engine->processSequences([
            ['N', 'V'],
            ['V', 'DET'],
            ['DET', 'N'],
        ]);

        $cycle = $this->analyzer->findCycleThrough($this->graph, 'N');

        expect($cycle)->not->toBeNull();
        expect($cycle)->toContain('N');
    });

    it('findCycleThrough returns null when no cycle exists', function () {
        $this->engine->processSequences([
            ['DET', 'N', 'V'],
        ]);

        $cycle = $this->analyzer->findCycleThrough($this->graph, 'DET');

        expect($cycle)->toBeNull();
    });
});
