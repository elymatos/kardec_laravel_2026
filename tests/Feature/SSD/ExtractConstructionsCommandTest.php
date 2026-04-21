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
use App\Services\SSD\CompetitiveConstructionDiscovery;
use App\Services\SSD\RelationAffinityCalculator;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->graphId = SsdGraph::create('Extraction Test', null, [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.01,
    ]);
});

afterEach(function () {
    Criteria::table('ssd_construction_interfaces')->where('graph_id', $this->graphId)->delete();
    SsdConstructionMember::deleteByGraph($this->graphId);
    SsdConstruction::deleteByGraph($this->graphId);
    SsdHigherOrderLink::deleteByGraph($this->graphId);
    SsdRelationNode::deleteByGraph($this->graphId);
    SsdElementCategory::deleteByGraph($this->graphId);
    SsdFunctionalCategory::deleteByGraph($this->graphId);
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

/**
 * Helper to run the full pipeline through reification so competitive discovery can run.
 */
function processAndReify(int $graphId, array $sequences): void
{
    foreach ($sequences as $seq) {
        Artisan::call('ssd:process', [
            'graph_id' => $graphId,
            '--inline' => implode(' ', $seq),
        ]);
    }
    Artisan::call('ssd:reify-relations', [
        'graph_id' => $graphId,
    ]);
}

describe('ssd:extract-constructions', function () {
    it('requires reification and fails gracefully if not reified', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET NOUN FIN',
        ])->assertSuccessful();

        // Without reification, extract-constructions should fail or warn (not crash)
        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertFailed();
    });

    it('extracts constructions after processing and reifying sequences', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
            ['DET', 'NOUN', 'FIN', 'DET', 'NOUN'],
            ['PPER', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $constructions = SsdConstruction::listByGraph($this->graphId);
        expect(count($constructions))->toBeGreaterThan(0);
    });

    it('stores construction members with relation_node_id', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'NOUN', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $constructions = SsdConstruction::listByGraph($this->graphId);
        expect(count($constructions))->toBeGreaterThan(0);

        $firstMembers = SsdConstructionMember::listByConstruction($constructions[0]->id);
        expect(count($firstMembers))->toBeGreaterThan(0);

        // Members must have relation_node_id set
        foreach ($firstMembers as $member) {
            expect($member->relation_node_id)->not->toBeNull();
        }
    });

    it('constructions have element_count set', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $constructions = SsdConstruction::listByGraph($this->graphId);
        foreach ($constructions as $c) {
            expect((int) $c->element_count)->toBeGreaterThan(0);
        }
    });

    it('labels constructions with default categories after categorize step', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
            ['PPER', 'FIN', 'PREP'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        $constructions = SsdConstruction::listByGraph($this->graphId);
        expect(count($constructions))->toBeGreaterThan(0);

        $labels = array_map(fn ($c) => $c->label, $constructions);
        // With categories, labels should not all be 'Unlabeled Construction'
        expect($labels)->not->toEqual(array_fill(0, count($labels), 'Unlabeled Construction'));
    });

    it('CompetitiveConstructionDiscovery throws when graph not reified', function () {
        $graph = SsdGraph::byId($this->graphId);
        $discovery = new CompetitiveConstructionDiscovery(
            new RelationAffinityCalculator,
            config('ssd.competition')
        );

        expect(fn () => $discovery->discover($graph))
            ->toThrow(RuntimeException::class, 'has not been reified');
    });

    it('affinity matrix is non-empty after reification with higher-order links', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
            ['PPER', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $graph = SsdGraph::byId($this->graphId);
        $calculator = new RelationAffinityCalculator;
        $matrix = $calculator->buildAffinityMatrix($graph);

        expect($matrix)->not->toBeEmpty();
    });

    it('coalitions are assigned after extraction', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
            ['DET', 'NOUN', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
        ])->assertSuccessful();

        // Check that relation nodes have coalition_id assigned
        $withCoalition = Criteria::table('ssd_relation_nodes')
            ->where('graph_id', $this->graphId)
            ->whereNotNull('coalition_id')
            ->count();

        expect($withCoalition)->toBeGreaterThan(0);
    });

    it('shows affinity matrix with --show-affinity flag', function () {
        $sequences = [
            ['DET', 'NOUN', 'FIN'],
            ['DET', 'ADJ', 'NOUN', 'FIN'],
        ];
        processAndReify($this->graphId, $sequences);

        $this->artisan('ssd:extract-constructions', [
            'graph_id' => $this->graphId,
            '--show-affinity' => true,
        ])->assertSuccessful();
    });
});
