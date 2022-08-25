<?php

namespace App\Console\Commands;

use App\Models\Locality;
use App\Models\Municipality;
use Illuminate\Console\Command;

class ReadAndInsertLocalities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:localities {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read localities from a specified file and save the records in the database.';

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
        // first, lets check if localities already exist
        if (Locality::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if localities already exist, which means this script has already been run.
            $this->error('Localities already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // only save locality if name is not empty
            if ($settlementData[5] !== '') {

                // get locality information
                // name gets sanitized, no accents and all uppercase
                $localityName = strtoupper(strip_accents($settlementData[5]));

                // localities will get inserted as we walk through the file
                // if it doesn't exist, insert it
                if (Locality::where('name', '=', $localityName)->doesntExist()) {
                    // we just store the name since its only used as reference
                    // @todo: find out if localities are related to municipalities or any other model
                    Locality::create(['name' => $localityName]);
                }
            }
        }

        return 0;
    }
}
