<?php

use App\Database\Criteria;
use Illuminate\Support\Facades\File;

describe('CheckLemmasWithoutLUsCommand', function () {
    it('displays help information when called with --help', function () {
        $this->artisan('lexicon:check-lemmas-without-lus', ['--help' => true])
            ->assertSuccessful();
    });

    it('fails when input file does not exist', function () {
        $this->artisan('lexicon:check-lemmas-without-lus', [
            '--input' => 'non-existent-file.csv',
        ])
            ->assertFailed();
    });

    it('processes the CSV file and creates output', function () {
        $outputPath = base_path('test-output-lemmas-without-lus.csv');

        // Clean up any existing output file
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }

        $this->artisan('lexicon:check-lemmas-without-lus', [
            '--output' => 'test-output-lemmas-without-lus.csv',
        ])
            ->assertSuccessful();

        // Verify output file was created
        expect(File::exists($outputPath))->toBeTrue();

        // Clean up
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('output CSV has correct headers', function () {
        $outputPath = base_path('test-output-lemmas-without-lus.csv');

        // Clean up any existing output file
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }

        $this->artisan('lexicon:check-lemmas-without-lus', [
            '--output' => 'test-output-lemmas-without-lus.csv',
        ])
            ->assertSuccessful();

        // Read the CSV and check headers
        $handle = fopen($outputPath, 'r');
        $headers = fgetcsv($handle);
        fclose($handle);

        expect($headers)->toBe([
            'idLexiconPattern',
            'idLemma',
            'mweLemma',
            'mweName',
            'totalNodes',
            'nodesWithLemma',
            'missingPositions',
            'hasLU',
            'luName',
            'frameName',
        ]);

        // Clean up
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('correctly identifies lemmas without LUs', function () {
        $outputPath = base_path('test-output-lemmas-without-lus.csv');

        // Clean up any existing output file
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }

        $this->artisan('lexicon:check-lemmas-without-lus', [
            '--output' => 'test-output-lemmas-without-lus.csv',
        ])
            ->assertSuccessful();

        // Read the output and verify hasLU flags are correct
        if (File::exists($outputPath)) {
            $handle = fopen($outputPath, 'r');
            $headers = fgetcsv($handle); // Skip header

            while (($row = fgetcsv($handle)) !== false) {
                $idLemma = (int) $row[1];
                $hasLU = $row[7];

                // Verify hasLU flag matches database
                $luExists = Criteria::table('lu')
                    ->where('idLemma', '=', $idLemma)
                    ->exists();

                if ($hasLU === 'Yes') {
                    expect($luExists)->toBeTrue();
                    expect($row[8])->not->toBeEmpty(); // luName should not be empty
                } else {
                    expect($luExists)->toBeFalse();
                    expect($row[8])->toBeEmpty(); // luName should be empty
                    expect($row[9])->toBeEmpty(); // frameName should be empty
                }
            }

            fclose($handle);
            File::delete($outputPath);
        }
    });

    it('includes frame information for lemmas with LUs', function () {
        $outputPath = base_path('test-output-lemmas-without-lus.csv');

        // Clean up any existing output file
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }

        $this->artisan('lexicon:check-lemmas-without-lus', [
            '--output' => 'test-output-lemmas-without-lus.csv',
        ])
            ->assertSuccessful();

        // Read the output and verify frame information
        $foundLemmaWithLU = false;
        if (File::exists($outputPath)) {
            $handle = fopen($outputPath, 'r');
            $headers = fgetcsv($handle); // Skip header

            while (($row = fgetcsv($handle)) !== false) {
                $hasLU = $row[7];

                if ($hasLU === 'Yes') {
                    $foundLemmaWithLU = true;
                    $luName = $row[8];
                    $frameName = $row[9];

                    // LU name should not be empty
                    expect($luName)->not->toBeEmpty();
                    // Frame name should be a string (might be empty if LU has no frame)
                    expect($frameName)->toBeString();

                    break; // Just need to verify one
                }
            }

            fclose($handle);
            File::delete($outputPath);
        }

        expect($foundLemmaWithLU)->toBeTrue();
    });
});
