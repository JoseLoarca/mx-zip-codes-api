<?php

namespace App\Console\Commands;

use App\Models\Locality;
use App\Models\Municipality;
use App\Models\Settlement;
use App\Models\SettlementType;
use App\Models\ZipCode;
use Illuminate\Console\Command;

class ReadAndInsertSettlements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:settlements {file=CodigosPostalesMX.txt : The file to be read}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will read settlements from a specified file and save the records in the database.';

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
        // first, lets check if settlements already exist
        if (Settlement::exists()) {
            // Since this command should be used just for inserting data one single time,
            // we return an error if settlement already exist, which means this script has already been run.
            $this->error('Settlements already exist!');
            return 0;
        }

        // get file argument
        $file = $this->argument('file');

        // open the file
        $fileHandler = fopen($file, 'r');

        // empty locality, zip code and municipality objets
        $zipCodeObj = null;
        $municipality = null;

        // loop through file content using fgets
        while ($line = fgets($fileHandler)) {
            $settlementData = explode('|', $line);

            // get all info using destructuring
            list($zipCode, $settlementName, $settlementType, $municipalityName, , $localityName, , , , , ,
                $municipalityKey, $settlementKey, $zoneType, ,) = $settlementData;

            // find the settlement type id
            $settlementTypeId = SettlementType::where('name', '=', $settlementType)->first()->id;

            // find the locality id
            $localityId = $localityName === '' ? null : Locality::where('name', '=', $localityName)->first()->id;

            // the idea of setting these objects is that we dont perform multiple / unnecessary queries on every loop,
            // we just keep track of when the object changes by comparing the name and or key

            if (!$zipCodeObj || $zipCodeObj->zip_code !== $zipCode) {
                $zipCodeObj = ZipCode::where('zip_code', '=', $zipCode)->first();
            }

            if (!$municipality || ($municipality->name !== strtoupper(strip_accents($municipalityName)) &&
                    $municipality->municipality_key !== $municipalityKey)) {
                $municipality = Municipality::where('name', '=', strtoupper(strip_accents($municipalityName)))
                    ->where('municipality_key', '=', $municipalityKey)->first();
            }

            // set settlement array, settlements name gets sanitized
            $settlement = [
                'name' => strtoupper(strip_accents($settlementName)), 'settlement_key' => $settlementKey,
                'zone_type' => strtoupper($zoneType), 'settlement_type_id' => $settlementTypeId,
                'locality_id' => $localityId, 'zip_code_id' => $zipCodeObj->id, 'municipality_id' => $municipality->id
            ];

            // settlements will get inserted as we walk through the document
            Settlement::create($settlement);

        }

        return 0;
    }
}
