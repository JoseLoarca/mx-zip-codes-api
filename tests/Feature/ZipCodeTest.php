<?php

namespace Tests\Feature;

use App\Http\Resources\ZipCodeResource;
use App\Models\ZipCode;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ZipCodeTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable throttling so we can test 100+ requests in a short amount of time
        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

    /**
     * Test the API returns a Not Found exception when:
     * 1. The root path is requested ('/')
     * 2. The zip codes route ('api/zip-codes/{zipCode}') is requested with the zipCode param missing
     * 3. The zip codes route ('api/zip-codes/{zipCode}') is requested using invalid zip codes
     *
     * @return void
     */
    public function test_not_found_exception(): void
    {
        // Root path Not Found
        $rootPathResponse = $this->get('/');
        $rootPathResponse->assertNotFound();

        // Missing zip code param
        $missingParamResponse = $this->get('api/zip-codes/');
        $missingParamResponse->assertNotFound();

        // Invalid zip codes
        $uuidResponse = $this->get(route('get.zip_code', ['zipCode' => (string) Str::uuid()]));
        $uuidResponse->assertNotFound();

        $thisIsNotAZipCodeResponse = $this->get(route('get.zip_code', ['zipCode' => 'This is not a zip code!']));
        $thisIsNotAZipCodeResponse->assertNotFound();

        $randomStringResponse = $this->get(route('get.zip_code', ['zipCode' => Str::random()]));
        $randomStringResponse->assertNotFound();
    }

    /**
     * Test the zip codes route returns an OK status when requested with valid zip codes
     *
     * @return void
     */
    public function test_ok_status(): void
    {
        // get random zip codes
        $zipCodes = ZipCode::limit(100)->orderByRaw('RAND()')->select(['zip_code'])->get();

        // loop through zip codes and test response
        foreach ($zipCodes as $zipCode) {
            $response = $this->get(route('get.zip_code', ['zipCode' => $zipCode->zip_code]));
            $response->assertOk();
        }
    }

    /**
     * Test the zip codes route returns the correct JSON API resource for valid zip codes
     *
     * @return void
     */
    public function test_api_resources(): void
    {
        // get random zip codes
        $zipCodes = ZipCode::limit(100)->orderByRaw('RAND()')->get();

        // loop through zip codes and test response
        foreach ($zipCodes as $zipCode) {
            // get the zip code resource as array
            $zipCodeResource = (new ZipCodeResource($zipCode))->toJson();
            $resourceAsArray = json_decode($zipCodeResource, true);

            // make request
            $response = $this->get(route('get.zip_code', ['zipCode' => $zipCode->zip_code]));
            // assert ok just in case
            $response->assertOk();
            // the API must return the same JSON as the one generated using a resource
            $response->assertExactJson($resourceAsArray);
        }
    }

    /**
     * Test the zip codes route returns the appropriate federal entity name
     *
     * @return void
     */
    public function test_matching_federal_entity(): void
    {
        // This query will fetch a list of federal entities and the zip codes associated to them
        $federalEntities = DB::table('federal_entities')
            ->join('municipalities AS m', 'federal_entities.id', '=', 'm.federal_entity_id')
            ->join('settlements AS s', 'm.id', '=', 's.municipality_id')
            ->join('zip_codes AS zc', 's.zip_code_id', '=', 'zc.id')
            ->groupBy('federal_entities.id')->orderByRaw('RAND()')->limit(5)
            ->select(DB::raw('federal_entities.name, GROUP_CONCAT(DISTINCT zc.zip_code) AS zip_codes'))->get();

        // loop through federal entities
        foreach ($federalEntities as $federalEntity) {
            // grab just 20 zip codes per entity
            $zipCodes = array_slice(explode(',', $federalEntity->zip_codes), 0, 20);

            // loop through zip codes
            foreach ($zipCodes as $zipCode) {
                // make request
                $response = $this->get(route('get.zip_code', ['zipCode' => $zipCode]));
                // assert ok just in case
                $response->assertOk();
                // federal entity name in response must match federal entity name retrieved from db
                $response->assertJsonPath('federal_entity.name', $federalEntity->name);
            }
        }
    }
}
