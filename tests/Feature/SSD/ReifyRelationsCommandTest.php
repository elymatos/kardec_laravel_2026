<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdElementCategory;
use App\Repositories\SSD\SsdFunctionalCategory;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdHigherOrderLink;
use App\Repositories\SSD\SsdRelationNode;
use App\Repositories\SSD\SsdSequence;

beforeEach(function () {
    $this->graphId = SsdGraph::create('Reify Test', null, [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.01,
    ]);
});

afterEach(function () {
    SsdHigherOrderLink::deleteByGraph($this->graphId);
    SsdRelationNode::deleteByGraph($this->graphId);
    SsdElementCategory::deleteByGraph($this->graphId);
    SsdFunctionalCategory::deleteByGraph($this->graphId);
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

describe('ssd:reify-relations', function () {
    it('creates relation nodes for transition links', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $relationNodes = SsdRelationNode::listByGraph($this->graphId);
        // DET→N and N→V links (excluding START/END) should have relation nodes
        expect(count($relationNodes))->toBeGreaterThan(0);
    });

    it('creates higher-order links from stored sequences', function () {
        // Process 3+ element sequences to enable higher-order links
        foreach (['DET N V', 'DET ADJ N V'] as $seq) {
            $this->artisan('ssd:process', [
                'graph_id' => $this->graphId,
                '--inline' => $seq,
            ])->assertSuccessful();
        }

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $hoLinks = SsdHigherOrderLink::listByGraph($this->graphId);
        expect(count($hoLinks))->toBeGreaterThan(0);
    });

    it('fails gracefully when no sequences found and no file given', function () {
        // No sequences processed, no --file
        // Manually create a node/link structure without going through ssd:process
        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertFailed();
    });

    it('creates relation node labels from element names', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $nodes = SsdRelationNode::listByGraph($this->graphId);
        $labels = array_map(fn ($n) => $n->label, $nodes);

        expect($labels)->toContain('R_DET_N');
        expect($labels)->toContain('R_N_V');
    });

    it('stores node_id in ssd_relation_nodes after reification', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $relationNodes = SsdRelationNode::listByGraph($this->graphId);
        expect($relationNodes)->not->toBeEmpty();

        // Each relation node should have node_id pointing to its ssd_nodes entry
        foreach ($relationNodes as $rn) {
            expect($rn->node_id)->not->toBeNull();

            $ssdNode = Criteria::table('ssd_nodes')
                ->where('id', $rn->node_id)
                ->where('label', $rn->label)
                ->where('node_type', 'relation')
                ->first();
            expect($ssdNode)->not->toBeNull();
        }
    });

    it('replaces direct end→start links with two-leg links through relation node', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N',
        ])->assertSuccessful();

        // Before reification: direct end_DET → start_N link exists
        $directBefore = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('s.label', 'end_DET')
            ->where('t.label', 'start_N')
            ->count();
        expect($directBefore)->toBe(1);

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        // After reification: direct link is gone
        $directAfter = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('s.label', 'end_DET')
            ->where('t.label', 'start_N')
            ->count();
        expect($directAfter)->toBe(0);

        // Two-leg links exist: end_DET → R_DET_N and R_DET_N → start_N
        $leg1 = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('s.label', 'end_DET')
            ->where('t.label', 'R_DET_N')
            ->count();
        expect($leg1)->toBe(1);

        $leg2 = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('s.label', 'R_DET_N')
            ->where('t.label', 'start_N')
            ->count();
        expect($leg2)->toBe(1);

        // R_DET_N exists as a relation node in ssd_nodes
        $relNode = Criteria::table('ssd_nodes')
            ->where('graph_id', $this->graphId)
            ->where('label', 'R_DET_N')
            ->where('node_type', 'relation')
            ->first();
        expect($relNode)->not->toBeNull();
    });

    it('--dry-run shows plan without modifying the database', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $this->artisan('ssd:reify-relations', [
            'graph_id' => $this->graphId,
            '--dry-run' => true,
        ])->assertSuccessful();

        // No relation nodes created
        $relationNodes = SsdRelationNode::listByGraph($this->graphId);
        expect($relationNodes)->toBeEmpty();

        // Original direct links still exist
        $directLinks = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('s.node_type', 'end')
            ->where('t.node_type', 'start')
            ->whereNotNull('s.element')
            ->whereNotNull('t.element')
            ->count();
        expect($directLinks)->toBeGreaterThan(0);
    });
});
