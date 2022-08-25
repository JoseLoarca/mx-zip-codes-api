<?php

namespace App\Console\Commands;

use App\Models\ZipCode;
use Illuminate\Console\Command;

class ReadAndInsertZipCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:zip_codes {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read zip codes from a specified file and save the records in the database.';

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
        // first, lets check if zip codes already exist
        if (ZipCode::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if zip codes already exist, which means this script has already been run.
            $this->error('Zip Codes already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // zip codes array
        $zipCodes = [];

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // get zip codes
            $zipCode = $settlementData[0];

            // store them in array
            if (!in_array($zipCode, $zipCodes)) {
                $zipCodes[$zipCode] = ['zip_code' => $zipCode];
            }
        }

        // save records in database
        ZipCode::insert($zipCodes);

        return 0;
    }
}
