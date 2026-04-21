<?php

namespace App\Console\Commands\Metadata;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessLinkMetadataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metadata:process-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process link metadata from CSV and associate with items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $csvPath = base_path('temp/metadados_05_locais_with_links.csv');

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");

            return Command::FAILURE;
        }

        $this->info('Processing link metadata from CSV...');

        // Statistics
        $totalRows = 0;
        $instancesCreated = 0;
        $instancesExisting = 0;
        $relationsCreated = 0;
        $errors = 0;

        // Read CSV file
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $this->error('Failed to open CSV file');

            return Command::FAILURE;
        }

        // Skip header row
        fgetcsv($handle);

        // Count total rows for progress bar
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 3) {
                $rows[] = $data;
            }
        }
        fclose($handle);

        $totalRows = count($rows);
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();

        // Process each row
        foreach ($rows as $data) {
            try {
                $idItem = $data[0];
                $name = $data[1];
                $value = $data[2];

                // Step 1: Check if instance exists
                $instance = DB::selectOne(
                    'SELECT idInstance FROM ak_instance WHERE name = ? AND value = ?',
                    [$name, $value]
                );

                if ($instance) {
                    $idInstance = $instance->idInstance;
                    $instancesExisting++;
                } else {
                    // Create new instance
                    $result = DB::selectOne(
                        'SELECT instance_create(?, ?) as idInstance',
                        [$name, $value]
                    );
                    $idInstance = $result->idInstance;
                    $instancesCreated++;
                }

                // Step 2: Associate instance with "link" type
                try {
                    DB::statement("
                        SET @idRelationType = (SELECT idRelationType FROM ak_relationtype WHERE name = 'category');
                        SET @idEntityType = (SELECT idEntity FROM ak_type WHERE name = 'link');
                        SET @idEntityInstance = (SELECT idEntity FROM ak_instance WHERE idInstance = ?);

                        INSERT INTO ak_entityrelation(idRelationType, idEntity1, idEntity2)
                        SELECT @idRelationType, @idEntityType, @idEntityInstance
                        WHERE NOT EXISTS (
                            SELECT 1 FROM ak_entityrelation
                            WHERE idRelationType = @idRelationType
                            AND idEntity1 = @idEntityType
                            AND idEntity2 = @idEntityInstance
                        );
                    ", [$idInstance]);

                    $relationsCreated++;
                } catch (Exception $e) {
                    // Skip silently if relation already exists
                }

                // Step 3: Associate instance with item
                try {
                    DB::statement("
                        SET @idRelationType = (SELECT idRelationType FROM ak_relationtype WHERE name = 'metadata');
                        SET @idEntityType = (SELECT idEntity FROM ak_type WHERE name = 'link');
                        SET @idEntityInstance = (SELECT idEntity FROM ak_instance WHERE idInstance = ?);
                        SET @idEntityItem = (SELECT idEntity FROM ak_item WHERE idItem = ?);

                        INSERT INTO ak_entityrelation(idRelationType, idEntity1, idEntity2, idEntity3)
                        SELECT @idRelationType, @idEntityItem, @idEntityType, @idEntityInstance
                        WHERE NOT EXISTS (
                            SELECT 1 FROM ak_entityrelation
                            WHERE idRelationType = @idRelationType
                            AND idEntity1 = @idEntityItem
                            AND idEntity2 = @idEntityType
                            AND idEntity3 = @idEntityInstance
                        );
                    ", [$idInstance, $idItem]);

                    $relationsCreated++;
                } catch (Exception $e) {
                    // Skip silently if relation already exists
                }

            } catch (Exception $e) {
                $this->error("Error processing row (idItem: {$idItem}): ".$e->getMessage());
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display statistics
        $this->info('Processing completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total rows processed', $totalRows],
                ['Instances created', $instancesCreated],
                ['Instances existing', $instancesExisting],
                ['Relations created', $relationsCreated],
                ['Errors', $errors],
            ]
        );

        return Command::SUCCESS;
    }
}
