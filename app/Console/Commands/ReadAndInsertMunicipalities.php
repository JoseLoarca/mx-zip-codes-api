<?php

namespace App\Console\Commands;

use App\Models\FederalEntity;
use App\Models\Municipality;
use Illuminate\Console\Command;

class ReadAndInsertMunicipalities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:municipalities {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read municipalities from a specified file and save the records in the database.';

    /**
     * Execute the console command.
     *
     * * This script assumes the following:
     * 1. The file to be read doesn't have any headers
     * 2. The content is in the following order:
     * d_codigo|d_asenta|d_tipo_asenta|D_mnpio|d_estado|d_ciudad|d_CP|c_estado|c_oficina|c_CP|c_tipo_asenta|c_mnpio|id_asenta_cpcons|d_zona|c_cve_ciudad
     *
     * @return int
     */
    public function handle()
    {
        // first, lets check if municipalities already exist
        if (Municipality::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if municipalities already exist, which means this script has already been run.
            $this->error('Municipalities already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // null federal entity object
        $federalEntity = null;

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // get municipalities information
            // name gets sanitized, no accents and all uppercase
            $municipalityName = strtoupper(strip_accents($settlementData[3]));
            $municipalityKey = (int) $settlementData[11];

            // get the federal entity info so we can assign the municipality to it
            $federalEntityName = strtoupper(strip_accents($settlementData[4]));
            $federalEntityKey = (int) $settlementData[7];

            // the idea of setting this object is that we dont perform a query to the federal entities table on every loop,
            // we just keep track of when the object changes by comparing the name and key
            if (!$federalEntity || ($federalEntity->name !== $federalEntityName && (int) $federalEntity->federal_entity_key !== $federalEntityKey)) {
                $federalEntity = FederalEntity::where('name', '=', $federalEntityName)->where('federal_entity_key', '=',
                    $federalEntityKey)->first();
            }

            // municipalities will get inserted as we walk through the file
            // if it doesn't exist, insert it
            if (Municipality::where('name', '=', $municipalityName)->where('municipality_key', '=',
                $municipalityKey)->where('federal_entity_id', '=', $federalEntity->id)->doesntExist()) {
                Municipality::create([
                    'name' => $municipalityName, 'municipality_key' => $municipalityKey,
                    'federal_entity_id' => $federalEntity->id
                ]);
            }
        }

        return 0;
    }
}
