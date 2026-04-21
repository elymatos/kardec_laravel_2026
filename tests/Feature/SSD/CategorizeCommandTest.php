<?php

use App\Database\Criteria;
use App\Repositories\SSD\SsdElementCategory;
use App\Repositories\SSD\SsdFunctionalCategory;
use App\Repositories\SSD\SsdGraph;
use App\Repositories\SSD\SsdSequence;

beforeEach(function () {
    $this->graphId = SsdGraph::create('Categorize Test', null, [
        'decay_factor' => 0.99,
        'reinforcement_amount' => 1.0,
        'initial_weight' => 1.0,
        'prune_threshold' => 0.01,
    ]);
});

afterEach(function () {
    SsdElementCategory::deleteByGraph($this->graphId);
    SsdFunctionalCategory::deleteByGraph($this->graphId);
    SsdSequence::deleteByGraph($this->graphId);
    Criteria::table('ssd_links')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_nodes')->where('graph_id', $this->graphId)->delete();
    Criteria::table('ssd_graphs')->where('id', $this->graphId)->delete();
});

describe('ssd:categorize', function () {
    it('--show shows empty categorization when nothing assigned', function () {
        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--show' => true,
        ])->assertSuccessful();
    });

    it('--defaults assigns categories to graph elements', function () {
        // First process a sequence so elements exist
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET NOUN FIN',
        ])->assertSuccessful();

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        // DET should be modifier
        $detCat = SsdElementCategory::findByElement($this->graphId, 'DET');
        expect($detCat)->not->toBeNull()
            ->and($detCat->category_name)->toBe('modifier');

        // NOUN should be referent
        $nounCat = SsdElementCategory::findByElement($this->graphId, 'NOUN');
        expect($nounCat)->not->toBeNull()
            ->and($nounCat->category_name)->toBe('referent');

        // FIN should be predicator
        $finCat = SsdElementCategory::findByElement($this->graphId, 'FIN');
        expect($finCat)->not->toBeNull()
            ->and($finCat->category_name)->toBe('predicator');
    });

    it('--defaults derives relation types after categorization', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET NOUN FIN',
        ])->assertSuccessful();

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        // DET→NOUN should be mod→ref (within)
        $links = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('l.link_type', 'transition')
            ->where('s.element', 'DET')
            ->where('t.element', 'NOUN')
            ->where('s.node_type', 'end')
            ->where('t.node_type', 'start')
            ->select('l.*')
            ->get()
            ->all();

        expect($links)->not->toBeEmpty();
        expect($links[0]->relation_type)->toBe('mod→ref');
        expect($links[0]->relation_class)->toBe('within');

        // NOUN→FIN should be ref→pred (between)
        $nvLinks = Criteria::table('ssd_links as l')
            ->join('ssd_nodes as s', 'l.source_node_id', '=', 's.id')
            ->join('ssd_nodes as t', 'l.target_node_id', '=', 't.id')
            ->where('l.graph_id', $this->graphId)
            ->where('l.link_type', 'transition')
            ->where('s.element', 'NOUN')
            ->where('t.element', 'FIN')
            ->where('s.node_type', 'end')
            ->where('t.node_type', 'start')
            ->select('l.*')
            ->get()
            ->all();

        expect($nvLinks)->not->toBeEmpty();
        expect($nvLinks[0]->relation_type)->toBe('ref→pred');
        expect($nvLinks[0]->relation_class)->toBe('between');
    });

    it('--show displays categorization table', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N',
        ])->assertSuccessful();

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--defaults' => true,
        ])->assertSuccessful();

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--show' => true,
        ])
            ->expectsOutputToContain('Current categorization:')
            ->assertSuccessful();

        // Verify the DB state directly
        $detCat = SsdElementCategory::findByElement($this->graphId, 'DET');
        expect($detCat)->not->toBeNull()
            ->and($detCat->category_name)->toBe('modifier');
    });

    it('--file loads a custom JSON mapping', function () {
        $this->artisan('ssd:process', [
            'graph_id' => $this->graphId,
            '--inline' => 'DET N V',
        ])->assertSuccessful();

        $mapping = [
            'referent' => ['color' => '#AABBCC', 'elements' => ['N']],
            'predicator' => ['color' => '#DDEEFF', 'elements' => ['V']],
        ];

        $tmpFile = tempnam(sys_get_temp_dir(), 'ssd_cat_').'.json';
        file_put_contents($tmpFile, json_encode($mapping));

        $this->artisan('ssd:categorize', [
            'graph_id' => $this->graphId,
            '--file' => $tmpFile,
        ])->assertSuccessful();

        @unlink($tmpFile);

        $nCat = SsdElementCategory::findByElement($this->graphId, 'N');
        expect($nCat)->not->toBeNull()
            ->and($nCat->category_name)->toBe('referent');
    });
});
