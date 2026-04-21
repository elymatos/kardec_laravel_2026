<?php

it('generates a CSV with correct 5-column header and valid data rows', function () {
    $output = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';

    $this->artisan('fe:generate-concepts-csv', ['--output' => $output])
        ->assertSuccessful();

    expect(file_exists($output))->toBeTrue();

    $rows = array_map('str_getcsv', file($output));

    expect($rows[0])->toBe(['fe_concept', 'fe_concept_name', 'fe_id', 'frame_name', 'fe_name']);
    expect(count($rows))->toBeGreaterThan(1);

    foreach (array_slice($rows, 1) as $row) {
        expect(count($row))->toBe(5);
        expect($row[0])->toStartWith('FEconcept_');  // fe_concept
        expect($row[1])->not->toBeEmpty();            // fe_concept_name (leaf FE name)
        expect($row[2])->toMatch('/^\d+$/');           // fe_id is numeric (target FEs use abs frame ID)
        expect($row[3])->not->toBeEmpty();            // frame_name
        expect($row[4])->not->toBeEmpty();            // fe_name
    }

    unlink($output);
});

it('names each concept after the leaf-frame FE that seeded it', function () {
    $output = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';

    $this->artisan('fe:generate-concepts-csv', ['--output' => $output])
        ->assertSuccessful();

    $rows = array_map('str_getcsv', file($output));
    array_shift($rows); // remove header

    // fe_concept_name must be the fe_name of at least one row in the same concept
    $conceptNames = [];
    $conceptFENames = [];
    foreach ($rows as $row) {
        $concept = $row[0];
        $conceptNames[$concept] ??= $row[1];
        $conceptFENames[$concept][] = $row[4];
    }

    foreach ($conceptNames as $concept => $name) {
        expect(in_array($name, $conceptFENames[$concept], true))->toBeTrue();
    }

    unlink($output);
});

it('enforces the same-frame constraint: no concept has two FEs from the same frame', function () {
    $output = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';

    $this->artisan('fe:generate-concepts-csv', ['--output' => $output])
        ->assertSuccessful();

    $rows = array_map('str_getcsv', file($output));
    array_shift($rows);

    $conceptFrames = [];
    foreach ($rows as $row) {
        [$concept, , , $frame] = $row;
        expect(isset($conceptFrames[$concept][$frame]))->toBeFalse(
            "Concept {$concept} has two FEs from frame '{$frame}'"
        );
        $conceptFrames[$concept][$frame] = true;
    }

    unlink($output);
});

it('includes target FEs from frame-level relations', function () {
    $output = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';

    $this->artisan('fe:generate-concepts-csv', ['--output' => $output])
        ->assertSuccessful();

    $rows = array_map('str_getcsv', file($output));
    array_shift($rows);

    $hasTarget = false;
    foreach ($rows as $row) {
        if ($row[4] === 'target') {
            $hasTarget = true;
            break;
        }
    }

    expect($hasTarget)->toBeTrue('No target FEs found in the CSV');

    unlink($output);
});

it('produces deterministic output on repeated runs', function () {
    $out1 = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';
    $out2 = tempnam(sys_get_temp_dir(), 'fe_concepts_').'.csv';

    $this->artisan('fe:generate-concepts-csv', ['--output' => $out1])->assertSuccessful();
    $this->artisan('fe:generate-concepts-csv', ['--output' => $out2])->assertSuccessful();

    expect(file_get_contents($out1))->toBe(file_get_contents($out2));

    unlink($out1);
    unlink($out2);
});
