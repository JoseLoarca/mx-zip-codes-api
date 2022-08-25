<?php

namespace App\Console\Commands;

use App\Models\SettlementType;
use Illuminate\Console\Command;

class ReadAndInsertSettlementTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:settlement_types {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read settlement types from a specified file and save the records in the database.';

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
        // first, lets check if settlement types already exist
        if (SettlementType::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if settlement types already exist, which means this script has already been run.
            $this->error('Settlement types already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // settlement types array
        $settlementTypes = [];

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // get settlement type, base on the demo API, this field is stored as is, no stripping accents, etc
            $settlementType = $settlementData[2];

            // store them in array
            if (!in_array($settlementType, $settlementTypes)) {
                $settlementTypes[$settlementType] = ['name' => $settlementType];
            }
        }

        // save records in database
        SettlementType::insert($settlementTypes);

        return 0;
    }
}
