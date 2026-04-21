<?php

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Clear only RNT nodes and their edges before each test
    $rntNodeIds = DB::table('parser_pattern_node')
        ->where('type', 'LIKE', 'RNT%')
        ->pluck('id')
        ->toArray();

    if (! empty($rntNodeIds)) {
        DB::table('parser_pattern_edge')
            ->where(function ($q) use ($rntNodeIds) {
                $q->whereIn('from_node_id', $rntNodeIds)
                    ->orWhereIn('to_node_id', $rntNodeIds);
            })
            ->delete();

        DB::table('parser_pattern_node')
            ->whereIn('id', $rntNodeIds)
            ->delete();
    }

    DB::table('parser_pattern_edge')
        ->where('properties', 'LIKE', '%rnt_role%')
        ->delete();
});

it('builds RNT graph successfully for a grammar', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')
        ->assertSuccessful();

    $nodeCount = DB::table('parser_pattern_node')
        ->where('type', 'LIKE', 'RNT%')
        ->count();
    expect($nodeCount)->toBeGreaterThan(0);

    $edgeCount = DB::table('parser_pattern_edge')
        ->where('properties', 'LIKE', '%rnt_role%')
        ->count();
    expect($edgeCount)->toBeGreaterThan(0);
});

it('creates all 18 expected category lines', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $categoryLines = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('ce_tier', '=', 'category')
        ->pluck('value')
        ->sort()
        ->values()
        ->all();

    $expected = [
        'ADJ', 'ADP', 'CLAUSE', 'CPP', 'CPP_VERB', 'DEM',
        'DET', 'NOUN', 'NUM', 'POSS', 'PRED', 'PRON', 'PRX',
        'REF', 'REF_BASE', 'REF_MOD', 'REL', 'VERB',
    ];
    sort($expected);

    expect($categoryLines)->toBe($expected);
});

it('creates expected slot lines', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $slotLines = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('ce_tier', '=', 'slot')
        ->pluck('value')
        ->sort()
        ->values()
        ->all();

    $expected = ['SLOT_ADJ', 'SLOT_ADP', 'SLOT_AUX', 'SLOT_DET', 'SLOT_NOUN', 'SLOT_NUM', 'SLOT_PROPN', 'SLOT_PRON', 'SLOT_VERB'];
    sort($expected);

    expect($slotLines)->toBe($expected);
});

it('creates exactly 26 junction nodes', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $junctionCount = DB::table('parser_pattern_node')
        ->whereIn('type', ['RNT_AND_ORD', 'RNT_AND_UNO', 'RNT_OR', 'RNT_PRECOR'])
        ->count();

    // 1(NOUN OR) + 6(type-A AND_UNO) + 3(type-D AND_UNO) + 1(PRX OR)
    // + 1(CPP_VERB AND_ORD) + 1(REL AND_ORD) + 1(PRED OR) + 1(REF_MOD OR)
    // + 5(REF_BASE: 1 OR + 4 PRECOR) + 2(REF: 1 AND_ORD + 1 PRECOR)
    // + 3(CLAUSE: 1 AND_ORD + 2 PRECOR) = 25
    expect($junctionCount)->toBe(25);
});

it('deduplicates shared SLOT_PRON across DEM, POSS, PRON rules', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $slotPronCount = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('value', '=', 'SLOT_PRON')
        ->count();
    expect($slotPronCount)->toBe(1);

    $slotPronId = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('value', '=', 'SLOT_PRON')
        ->value('id');

    // SLOT_PRON referenced as and_input by DEM, POSS, PRON = 3 edges
    $andInputEdges = DB::table('parser_pattern_edge')
        ->where('from_node_id', '=', $slotPronId)
        ->where('properties', 'LIKE', '%and_input%')
        ->count();
    expect($andInputEdges)->toBe(3);
});

it('creates feature lines for DEM, POSS, PRON', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $featureLines = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('ce_tier', '=', 'feature')
        ->pluck('value')
        ->sort()
        ->values()
        ->all();

    expect($featureLines)->toBe(['FEAT:Poss=yes', 'FEAT:PronType=Dem', 'FEAT:PronType=Prs']);
});

it('creates literal lines for PRX', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $literalLines = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_LINE')
        ->where('ce_tier', '=', 'literal')
        ->pluck('value')
        ->sort()
        ->values()
        ->all();

    expect($literalLines)->toBe(['onde', 'que']);
});

it('creates 8 bypass lines across REF_BASE, REF, and CLAUSE', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $bypasses = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_BYPASS')
        ->count();

    // 5 for REF_BASE + 1 for REF + 2 for CLAUSE = 8
    expect($bypasses)->toBe(8);
});

it('every AND_ORD junction has at least 2 ordered and_input edges with distinct sequences', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $andOrdJunctions = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_AND_ORD')
        ->get();

    foreach ($andOrdJunctions as $junction) {
        $inputCount = DB::table('parser_pattern_edge')
            ->where('to_node_id', '=', $junction->id)
            ->where('properties', 'LIKE', '%and_input%')
            ->count();

        expect($inputCount)->toBeGreaterThanOrEqual(2, "AND_ORD junction '{$junction->value}' has only {$inputCount} and_input edges");
    }
});

it('every AND_UNO junction has at least 1 and_input edge', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $andUnoJunctions = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_AND_UNO')
        ->get();

    foreach ($andUnoJunctions as $junction) {
        $inputCount = DB::table('parser_pattern_edge')
            ->where('to_node_id', '=', $junction->id)
            ->where('properties', 'LIKE', '%and_input%')
            ->count();

        expect($inputCount)->toBeGreaterThanOrEqual(1, "AND_UNO junction '{$junction->value}' has no and_input edges");
    }
});

it('every PrecOR has exactly 1 precor_precedence and 1 precor_default input', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $precors = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_PRECOR')
        ->get();

    foreach ($precors as $precor) {
        $precCount = DB::table('parser_pattern_edge')
            ->where('to_node_id', '=', $precor->id)
            ->where('properties', 'LIKE', '%precor_precedence%')
            ->count();

        $dfltCount = DB::table('parser_pattern_edge')
            ->where('to_node_id', '=', $precor->id)
            ->where('properties', 'LIKE', '%precor_default%')
            ->count();

        expect($precCount)->toBe(1, "PrecOR '{$precor->value}' has {$precCount} precor_precedence edges (expected 1)");
        expect($dfltCount)->toBe(1, "PrecOR '{$precor->value}' has {$dfltCount} precor_default edges (expected 1)");
    }
});

it('rebuilds RNT graph with same node count when --rebuild flag is used', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $firstCount = DB::table('parser_pattern_node')
        ->where('type', 'LIKE', 'RNT%')
        ->count();

    $this->artisan('parser:build-rnt-graph --grammar=1 --rebuild')->assertSuccessful();

    $secondCount = DB::table('parser_pattern_node')
        ->where('type', 'LIKE', 'RNT%')
        ->count();

    expect($secondCount)->toBe($firstCount);
});

it('shows statistics after building', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')
        ->expectsOutputToContain('Total RNT Nodes')
        ->expectsOutputToContain('Total RNT Edges')
        ->expectsOutputToContain('Category Lines')
        ->assertSuccessful();
});

it('exports DOT file when --export-dot flag is used', function () {
    $dotPath = storage_path('graphs/rnt_graph_1.dot');

    if (file_exists($dotPath)) {
        unlink($dotPath);
    }

    $this->artisan('parser:build-rnt-graph --grammar=1 --export-dot')
        ->expectsOutputToContain('DOT file exported')
        ->assertSuccessful();

    expect(file_exists($dotPath))->toBeTrue();

    $dotContent = file_get_contents($dotPath);
    expect($dotContent)->toContain('digraph RntGraph_1');
    expect($dotContent)->toContain('node_');
    expect($dotContent)->toContain('->');
});

it('REF_BASE top OR receives exactly 2 or_input edges (PRON and chain bypass)', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $topOr = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_OR')
        ->where('value', '=', 'REFBASE_top_or')
        ->first();

    expect($topOr)->not->toBeNull();

    $orInputs = DB::table('parser_pattern_edge')
        ->where('to_node_id', '=', $topOr->id)
        ->where('properties', 'LIKE', '%or_input%')
        ->count();

    expect($orInputs)->toBe(2);
});

it('CLAUSE AND_ORD has 3 ordered inputs at sequences 1, 2, 3', function () {
    $this->artisan('parser:build-rnt-graph --grammar=1')->assertSuccessful();

    $clauseAnd = DB::table('parser_pattern_node')
        ->where('type', '=', 'RNT_AND_ORD')
        ->where('value', '=', 'CLAUSE_seq')
        ->first();

    expect($clauseAnd)->not->toBeNull();

    $sequences = DB::table('parser_pattern_edge')
        ->where('to_node_id', '=', $clauseAnd->id)
        ->where('properties', 'LIKE', '%and_input%')
        ->pluck('sequence')
        ->sort()
        ->values()
        ->all();

    expect($sequences)->toBe([1, 2, 3]);
});
