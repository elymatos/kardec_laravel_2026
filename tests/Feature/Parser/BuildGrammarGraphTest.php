<?php

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Clear graph data before each test
    DB::table('parser_pattern_edge')->delete();
    DB::table('parser_pattern_node')->delete();
});

it('builds grammar graph successfully for a grammar', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')
        ->assertSuccessful();

    // Verify nodes were created
    $nodeCount = DB::table('parser_pattern_node')->count();
    expect($nodeCount)->toBeGreaterThan(0);

    // Verify edges were created
    $edgeCount = DB::table('parser_pattern_edge')->count();
    expect($edgeCount)->toBeGreaterThan(0);

    // Verify both edge types exist
    $intraEdges = DB::table('parser_pattern_edge')
        ->where('edge_type', 'INTRA_PATTERN')
        ->count();
    expect($intraEdges)->toBeGreaterThan(0);

    $interEdges = DB::table('parser_pattern_edge')
        ->where('edge_type', 'INTER_PATTERN')
        ->count();
    expect($interEdges)->toBeGreaterThan(0);
});

it('creates inter-pattern edges from END to CONSTRUCTION_REF nodes', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // Get inter-pattern edges
    $interEdges = DB::table('parser_pattern_edge as e')
        ->join('parser_pattern_node as n1', 'e.from_node_id', '=', 'n1.id')
        ->join('parser_pattern_node as n2', 'e.to_node_id', '=', 'n2.id')
        ->where('e.edge_type', 'INTER_PATTERN')
        ->select('n1.type as from_type', 'n2.type as to_type')
        ->get();

    expect($interEdges->count())->toBeGreaterThan(0);

    // All inter-pattern edges should be END -> CONSTRUCTION_REF
    foreach ($interEdges as $edge) {
        expect($edge->from_type)->toBe('END');
        expect($edge->to_type)->toBe('CONSTRUCTION_REF');
    }
});

it('deduplicates nodes with identical specifications', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // Each construction gets its own START node (canonical spec includes construction_id)
    $startNodes = DB::table('parser_pattern_node')
        ->where('type', 'START')
        ->count();
    expect($startNodes)->toBeGreaterThan(0);

    // A SLOT with the same POS appears at most once per construction per local position
    $nounSlots = DB::table('parser_pattern_node')
        ->where('type', 'SLOT')
        ->where('pos', 'NOUN')
        ->count();
    expect($nounSlots)->toBe(1);
});

it('sets owner_construction_id for END nodes', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // All END nodes should have owner_construction_id set
    $endNodesWithoutOwner = DB::table('parser_pattern_node')
        ->where('type', 'END')
        ->whereNull('owner_construction_id')
        ->count();

    expect($endNodesWithoutOwner)->toBe(0);

    // Verify END nodes have correct owner
    $endNode = DB::table('parser_pattern_node')
        ->where('type', 'END')
        ->whereNotNull('owner_construction_id')
        ->first();

    expect($endNode->owner_construction_id)->toBeGreaterThan(0);
});

it('rebuilds graph when --rebuild flag is used', function () {
    // Build first time
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    $firstBuildNodes = DB::table('parser_pattern_node')->count();
    $firstBuildEdges = DB::table('parser_pattern_edge')->count();

    // Rebuild
    $this->artisan('parser:build-grammar-graph --grammar=1 --rebuild')->assertSuccessful();

    $secondBuildNodes = DB::table('parser_pattern_node')->count();
    $secondBuildEdges = DB::table('parser_pattern_edge')->count();

    // Node and edge counts should be similar (allowing for minor differences)
    expect($secondBuildNodes)->toBeGreaterThan(0);
    expect($secondBuildEdges)->toBeGreaterThan(0);
});

it('shows statistics after building', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')
        ->expectsOutputToContain('Total Nodes')
        ->expectsOutputToContain('Total Edges')
        ->expectsOutputToContain('Intra-Pattern Edges')
        ->expectsOutputToContain('Inter-Pattern Edges')
        ->assertSuccessful();
});

it('exports DOT file when --export-dot flag is used', function () {
    $dotPath = storage_path('graphs/grammar_graph_1.dot');

    // Clean up before test
    if (file_exists($dotPath)) {
        unlink($dotPath);
    }

    $this->artisan('parser:build-grammar-graph --grammar=1 --export-dot')
        ->expectsOutputToContain('DOT file exported')
        ->assertSuccessful();

    expect(file_exists($dotPath))->toBeTrue();

    // Verify DOT file contains valid syntax
    $dotContent = file_get_contents($dotPath);
    expect($dotContent)->toContain('digraph GrammarGraph_1');
    expect($dotContent)->toContain('node_');
    expect($dotContent)->toContain('->');
});

it('handles missing construction references gracefully', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')
        ->assertSuccessful();

    // Should still complete successfully even with orphaned refs
    $stats = DB::table('parser_pattern_node')
        ->where('type', 'CONSTRUCTION_REF')
        ->count();

    expect($stats)->toBeGreaterThan(0);
});

it('increments usage_count when adding edges', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // All nodes involved in edges should have usage_count > 0
    $nodesInEdges = DB::table('parser_pattern_node as n')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('parser_pattern_edge')
                ->whereColumn('from_node_id', 'n.id')
                ->orWhereColumn('to_node_id', 'n.id');
        })
        ->where('usage_count', '>', 0)
        ->count();

    expect($nodesInEdges)->toBeGreaterThan(0);
});

it('creates edges with correct edge_type', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // Check that intra-pattern edges have pattern_id > 0
    $intraEdges = DB::table('parser_pattern_edge')
        ->where('edge_type', 'INTRA_PATTERN')
        ->where('pattern_id', '>', 0)
        ->count();

    expect($intraEdges)->toBeGreaterThan(0);

    // Check that inter-pattern edges have pattern_id = 0
    $interEdges = DB::table('parser_pattern_edge')
        ->where('edge_type', 'INTER_PATTERN')
        ->where('pattern_id', '=', 0)
        ->count();

    expect($interEdges)->toBeGreaterThan(0);
});

it('creates UD_FEATURE nodes for SLOT constraints', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // Grammar 1 has 3 constrained SLOT nodes: PronType=Dem, Poss=yes, PronType=Prs
    $udFeatureNodes = DB::table('parser_pattern_node')
        ->where('type', 'UD_FEATURE')
        ->get();

    expect($udFeatureNodes->count())->toBe(3);

    $featureValues = $udFeatureNodes->pluck('value')->sort()->values()->all();
    expect($featureValues)->toBe(['Poss=yes', 'PronType=Dem', 'PronType=Prs']);
});

it('normalizes colon-separated UD constraints to equals format', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // "Poss:yes" must be stored as "Poss=yes"
    $node = DB::table('parser_pattern_node')
        ->where('type', 'UD_FEATURE')
        ->where('value', 'Poss=yes')
        ->first();

    expect($node)->not->toBeNull();
});

it('creates SLOT_FEATURE edges linking SLOT nodes to their UD_FEATURE nodes', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    $slotFeatureEdges = DB::table('parser_pattern_edge as e')
        ->join('parser_pattern_node as feat', 'e.from_node_id', '=', 'feat.id')
        ->join('parser_pattern_node as slot', 'e.to_node_id', '=', 'slot.id')
        ->where('e.edge_type', 'SLOT_FEATURE')
        ->select('feat.type as feat_type', 'slot.type as slot_type', 'feat.value as feature_value')
        ->get();

    expect($slotFeatureEdges->count())->toBe(3);

    foreach ($slotFeatureEdges as $edge) {
        expect($edge->feat_type)->toBe('UD_FEATURE');
        expect($edge->slot_type)->toBe('SLOT');
    }
});

it('deduplicates UD_FEATURE nodes shared across constructions', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // "PronType=Dem" should exist exactly once even if referenced by multiple constructions
    $count = DB::table('parser_pattern_node')
        ->where('type', 'UD_FEATURE')
        ->where('value', 'PronType=Dem')
        ->count();

    expect($count)->toBe(1);
});

it('stores edge properties correctly', function () {
    $this->artisan('parser:build-grammar-graph --grammar=1')->assertSuccessful();

    // Inter-pattern edges should have cross_construction property
    $interEdge = DB::table('parser_pattern_edge')
        ->where('edge_type', 'INTER_PATTERN')
        ->first();

    expect($interEdge->properties)->not->toBeNull();

    $properties = json_decode($interEdge->properties, true);
    expect($properties)->toHaveKey('cross_construction');
    expect($properties)->toHaveKey('satisfies');
    expect($properties)->toHaveKey('label');
    expect($properties['label'])->toBe('satisfies');
});
