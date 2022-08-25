<?php

namespace App\Console\Commands;

use App\Models\FederalEntity;
use Illuminate\Console\Command;
use App\Models\Settlement;

class ReadAndInsertFederalEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:federal_entities {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read federal entities from a specified file and save the records in the database.';

    /**
     * Execute the console command.
     *
     * This script assumes the following:
     * 1. The file to be read doesn't have any headers
     * 2. The content is in the following order:
     * d_codigo|d_asenta|d_tipo_asenta|D_mnpio|d_estado|d_ciudad|d_CP|c_estado|c_oficina|c_CP|c_tipo_asenta|c_mnpio|id_asenta_cpcons|d_zona|c_cve_ciudad
     *
     * @return int
     */
    public function handle()
    {
        // first, lets check if federal entities already exist
        if (FederalEntity::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if federal entities already exist, which means this script has already been run.
            $this->error('Federal entities already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // Federal entities array
        $federalEntities = [];

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // get federal entities information
            $federalEntityName = $settlementData[4];
            $federalEntityKey = $settlementData[7];

            // sanitized name: no accents, all uppercase
            $sanitizedName = strtoupper(strip_accents($federalEntityName));

            // store them in array
            if (!in_array($sanitizedName, $federalEntities)) {
                $federalEntities[$sanitizedName] = [
                    'name' => $sanitizedName, 'federal_entity_key' => $federalEntityKey, 'code' => null
                ];
            }
        }

        // save records in database
        FederalEntity::insert($federalEntities);

        return 0;
    }
}
