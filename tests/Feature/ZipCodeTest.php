<?php

namespace Tests\Feature;

use App\Http\Resources\ZipCodeResource;
use App\Models\ZipCode;
use Illuminate\Routing\Middleware\ThrottleRequests;
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
            $response = $this->get(route('get.zip_code', ['zipCode' => $zipCode]));
            // assert ok just in case
            $response->assertOk();
            // the API must return the same JSON as the one generated using a resource
            $response->assertExactJson($resourceAsArray);
        }
    }
}
