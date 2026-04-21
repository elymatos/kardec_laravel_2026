<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdLink;
use App\Repositories\SSD\SsdNode;
use App\Repositories\SSD\SsdSequence;
use App\Services\SSD\GraphEngine;

beforeEach(function () {
    $this->graphId = SsdGraph::create('Test Graph', 'Unit test graph', [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.01,
        'activation_propagation_factor' => 0.5,
    ]);

    $this->graph = SsdGraph::byId($this->graphId);
    $this->engine = new GraphEngine($this->graph);
});

afterEach(function () {
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

describe('GraphEngine', function () {
    it('creates tripartite nodes for a new element', function () {
        $this->engine->ensureElement('DET');

        $startNode = SsdNode::findByLabel($this->graphId, 'start_DET');
        $elementNode = SsdNode::findByLabel($this->graphId, 'DET');
        $endNode = SsdNode::findByLabel($this->graphId, 'end_DET');

        expect($startNode)->not->toBeNull()
            ->and($startNode->node_type)->toBe('start')
            ->and($elementNode)->not->toBeNull()
            ->and($elementNode->node_type)->toBe('element')
            ->and($endNode)->not->toBeNull()
            ->and($endNode->node_type)->toBe('end');
    });

    it('creates START and END boundary nodes', function () {
        $this->engine->ensureBoundaries();

        $start = SsdNode::findByLabel($this->graphId, 'START');
        $end = SsdNode::findByLabel($this->graphId, 'END');

        expect($start)->not->toBeNull()
            ->and($start->node_type)->toBe('boundary')
            ->and($end)->not->toBeNull()
            ->and($end->node_type)->toBe('boundary');
    });

    it('creates transition links when processing a sequence', function () {
        $this->engine->processSequence(['DET', 'N', 'V']);

        $links = SsdLink::listTransitions($this->graphId);

        // Expect START→start_DET, end_DET→start_N, end_N→start_V, end_V→END
        expect(count($links))->toBe(4);

        $labels = array_map(function ($link) {
            $src = SsdNode::findByLabel($this->graphId, '');
            $srcNode = Criteria::table('ssd_nodes')->where('id', $link->source_node_id)->first();
            $tgtNode = Criteria::table('ssd_nodes')->where('id', $link->target_node_id)->first();

            return $srcNode->label.'->'.$tgtNode->label;
        }, $links);

        expect($labels)->toContain('START->start_DET')
            ->and($labels)->toContain('end_DET->start_N')
            ->and($labels)->toContain('end_N->start_V')
            ->and($labels)->toContain('end_V->END');
    });

    it('strengthens existing links on repeated sequence', function () {
        $this->engine->processSequence(['DET', 'N']);
        $this->engine->processSequence(['DET', 'N']);

        $links = SsdLink::listTransitions($this->graphId);
        $detToN = null;

        foreach ($links as $link) {
            $srcNode = Criteria::table('ssd_nodes')->where('id', $link->source_node_id)->first();
            $tgtNode = Criteria::table('ssd_nodes')->where('id', $link->target_node_id)->first();
            if ($srcNode->label === 'end_DET' && $tgtNode->label === 'start_N') {
                $detToN = $link;
                break;
            }
        }

        expect($detToN)->not->toBeNull();
        // initial_weight=1, reinforcement_amount=1 per process; decay=0.99 applied on 2nd
        // After 1st: weight=initial(1)+reinforce(1) = 2.0
        // After 2nd decay: weight=2.0*0.99=1.98, then +reinforce(1) = 2.98
        expect($detToN->reinforcement_count)->toBe(2);
        expect((float) $detToN->weight)->toBeGreaterThan(2.0);
    });

    it('applies decay to transition link weights', function () {
        $this->engine->processSequence(['N', 'V']);

        // First process: START->start_N gets initial(1)+reinforce(1) = 2.0
        $linksBefore = SsdLink::listTransitions($this->graphId);
        $startToN = null;
        foreach ($linksBefore as $link) {
            $srcNode = Criteria::table('ssd_nodes')->where('id', $link->source_node_id)->first();
            $tgtNode = Criteria::table('ssd_nodes')->where('id', $link->target_node_id)->first();
            if ($srcNode->label === 'START' && $tgtNode->label === 'start_N') {
                $startToN = $link;
                break;
            }
        }
        $weightAfterFirst = (float) $startToN->weight;

        // Second process applies decay first: weight = weight * 0.99
        $this->engine->processSequence(['V']);
        $linksAfter = SsdLink::listTransitions($this->graphId);

        $startToN2 = null;
        foreach ($linksAfter as $link) {
            $srcNode = Criteria::table('ssd_nodes')->where('id', $link->source_node_id)->first();
            $tgtNode = Criteria::table('ssd_nodes')->where('id', $link->target_node_id)->first();
            if ($srcNode->label === 'START' && $tgtNode->label === 'start_N') {
                $startToN2 = $link;
                break;
            }
        }

        expect($startToN2)->not->toBeNull();
        expect((float) $startToN2->weight)->toBeLessThan($weightAfterFirst);
    });

    it('prunes links below threshold', function () {
        // Use a graph with very aggressive decay and high threshold so links die quickly
        $graphId = SsdGraph::create('Prune Test', null, [
            'decay_factor' => 0.1,  // very aggressive decay
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.5,  // prune if weight < 0.5
        ]);
        $graph = SsdGraph::byId($graphId);
        $engine = new GraphEngine($graph);

        $engine->processSequence(['X', 'Y']);

        // After first process the links have weight=initial(1)+reinforce(1)=2.0
        $linksAfterFirst = SsdLink::listTransitions($graphId);
        expect(count($linksAfterFirst))->toBeGreaterThan(0);

        // Second process: apply decay 0.1 → weight=2.0*0.1=0.2 < threshold 0.5 → pruned
        $engine->processSequence(['Z']);

        $linksAfterSecond = SsdLink::listTransitions($graphId);

        // START->start_X and end_X->start_Y and end_Y->END should all be pruned
        $remainingXY = array_filter($linksAfterSecond, function ($link) {
            $srcNode = Criteria::table('ssd_nodes')->where('id', $link->source_node_id)->first();
            $tgtNode = Criteria::table('ssd_nodes')->where('id', $link->target_node_id)->first();

            return in_array($srcNode->label, ['START', 'end_X', 'end_Y'], true)
                && in_array($tgtNode->label, ['start_X', 'start_Y', 'END'], true);
        });

        expect(count($remainingXY))->toBe(0);

        // Cleanup
        SsdSequence::deleteByGraph($graphId);
        Criteria::table('ssd_links')->where('graph_id', $graphId)->delete();
        Criteria::table('ssd_nodes')->where('graph_id', $graphId)->delete();
        Criteria::table('ssd_graphs')->where('id', $graphId)->delete();
    });

    it('returns continuations with correct weights', function () {
        $this->engine->processSequence(['DET', 'N', 'V']);
        $this->engine->processSequence(['DET', 'ADJ', 'N']);

        $continuations = $this->engine->getContinuations('DET');

        expect(count($continuations))->toBeGreaterThan(0);

        $elements = array_column($continuations, 'element');
        expect($elements)->toContain('N')
            ->and($elements)->toContain('ADJ');
    });

    it('returns predecessors with correct weights', function () {
        $this->engine->processSequence(['DET', 'N', 'V']);
        $this->engine->processSequence(['PRON_PERS', 'V']);

        $predecessors = $this->engine->getPredecessors('V');

        expect(count($predecessors))->toBeGreaterThan(0);

        $elements = array_column($predecessors, 'element');
        expect($elements)->toContain('N')
            ->and($elements)->toContain('PRON_PERS');
    });

    it('getStatistics returns expected keys', function () {
        $this->engine->processSequence(['N', 'V']);

        $stats = $this->engine->getStatistics();

        expect($stats)->toHaveKey('total_nodes')
            ->and($stats)->toHaveKey('total_links')
            ->and($stats)->toHaveKey('total_transition_links')
            ->and($stats)->toHaveKey('avg_weight')
            ->and($stats)->toHaveKey('max_weight')
            ->and($stats)->toHaveKey('min_weight')
            ->and($stats)->toHaveKey('sequences_processed')
            ->and($stats['sequences_processed'])->toBe(1)
            ->and($stats['total_transition_links'])->toBeGreaterThan(0);
    });

    it('stores sequences in ssd_sequences on processSequence', function () {
        $this->engine->processSequence(['DET', 'N']);
        $this->engine->processSequence(['N', 'V']);

        $sequences = SsdSequence::listByGraph($this->graphId);

        expect(count($sequences))->toBe(2);

        $first = json_decode($sequences[0]->sequence, true);
        expect($first)->toBe(['DET', 'N']);

        $second = json_decode($sequences[1]->sequence, true);
        expect($second)->toBe(['N', 'V']);
    });

    it('processes multiple sequences and builds expected strong links', function () {
        $sequences = [
            ['DET', 'N', 'V'],
            ['DET', 'ADJ', 'N', 'V'],
            ['DET', 'ADJ', 'N', 'V', 'DET', 'N'],
            ['PRON_PERS', 'V', 'ADV'],
            ['PRON_PERS', 'V', 'DET', 'N'],
        ];

        $this->engine->processSequences($sequences);

        $stats = $this->engine->getStatistics();
        expect($stats['sequences_processed'])->toBe(5);

        // DET → N should be strong (appears in 3 sequences)
        $continuationsAfterDet = $this->engine->getContinuations('DET');
        $detToN = array_values(array_filter($continuationsAfterDet, fn ($c) => $c['element'] === 'N'));

        expect(count($detToN))->toBeGreaterThan(0)
            ->and($detToN[0]['weight'])->toBeGreaterThan(1.0);

        // N → V should also be present
        $continuationsAfterN = $this->engine->getContinuations('N');
        $nToV = array_values(array_filter($continuationsAfterN, fn ($c) => $c['element'] === 'V'));
        expect(count($nToV))->toBeGreaterThan(0);
    });
});
