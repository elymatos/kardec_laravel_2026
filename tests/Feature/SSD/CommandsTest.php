<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdSequence;

beforeEach(function () {
    $this->graphId = null;
});

afterEach(function () {
    if ($this->graphId !== null) {
        SsdSequence::deleteByGraph($this->graphId);
        Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
        Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
        Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
    }
});

describe('SSD Commands', function () {
    it('ssd:create creates a graph and shows its ID', function () {
        // Do NOT assign to a variable — the command runs via __destruct() at end of statement.
        $this->artisan('ssd:create', [
            'name' => 'Command Test Graph',
            '--description' => 'Created by command test',
        ])->assertSuccessful();

        // Command has run; query is safe now.
        $graph = Criteria::table('ssd_graphs')
            ->where('name', 'Command Test Graph')
            ->orderBy('id', 'desc')
            ->first();

        $this->graphId = $graph?->id;

        expect($graph)->not->toBeNull()
            ->and($graph->name)->toBe('Command Test Graph');

        $config = json_decode($graph->config, true);
        expect($config)->toHaveKey('decay_factor')
            ->and($config['decay_factor'])->toBe(0.99);
    });

    it('ssd:process --inline processes a sequence and shows stats', function () {
        $this->graphId = SsdGraph::create('Process Test', null, [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
        ]);

        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])
            ->expectsOutputToContain('Sequences processed')
            ->assertSuccessful();

        $graph = SsdGraph::byId($this->graphId);
        expect((int) $graph->sequences_processed)->toBe(1);
    });

    it('ssd:inspect --stats shows statistics table', function () {
        $this->graphId = SsdGraph::create('Inspect Stats Test', null, [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
        ]);

        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N',
        ])->assertSuccessful();

        $this->artisan('ssd:inspect', [
            'graph_id' => $this->graphId,
            '--stats' => true,
        ])
            ->expectsOutputToContain('Sequences processed')
            ->assertSuccessful();
    });

    it('ssd:inspect --element shows continuations and predecessors', function () {
        $this->graphId = SsdGraph::create('Inspect Element Test', null, [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
        ]);

        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $this->artisan('ssd:inspect', [
            'graph_id' => $this->graphId,
            '--element' => 'N',
        ])
            ->expectsOutputToContain('Continuations')
            ->expectsOutputToContain('Predecessors')
            ->assertSuccessful();
    });

    it('ssd:reset without --confirm returns failure', function () {
        $this->graphId = SsdGraph::create('Reset Test', null, [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
        ]);

        $this->artisan('ssd:reset', ['graph_id' => $this->graphId])
            ->assertFailed();
    });

    it('ssd:reset with --confirm deletes all nodes and links', function () {
        $this->graphId = SsdGraph::create('Reset Confirm Test', null, [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
        ]);

        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $nodesBefore = Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->count();
        expect($nodesBefore)->toBeGreaterThan(0);

        $this->artisan('ssd:reset', [
            'graph_id' => $this->graphId,
            '--confirm' => true,
        ])->assertSuccessful();

        $nodesAfter = Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->count();
        $linksAfter = Criteria::table('ssd_links')->where('graph_id', $this->graphId)->count();

        expect($nodesAfter)->toBe(0)
            ->and($linksAfter)->toBe(0);
    });
});
