<?php

namespace App\Http\Controllers;

use App\Http\Resources\ZipCodeResource;
use App\Models\ZipCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ZipCodeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param  string  $zipCode
     *
     * @return ZipCodeResource|JsonResponse
     */
    public function __invoke(Request $request, string $zipCode)
    {
        // retrieve zip code information from cache
        $cachedZipCode = Redis::get('zip_code_' . $zipCode);

        // If zip is in cache, return the cached information
        if ($cachedZipCode) {
            $zipCodeResponse = json_decode($cachedZipCode, true);
            return response()->json($zipCodeResponse);
        }

        // Else, we try to fetch zip code from db
        $zipCodeDb = ZipCode::where('zip_code', $zipCode)->firstOrFail();

        // if it exists on db, cache the resource and return it
        $zipCodeResource = new ZipCodeResource($zipCodeDb);

        // Store zip code in cache
        Redis::set('zip_code_' . $zipCode, $zipCodeResource->toResponse($request)->getContent());

        return $zipCodeResource;
    }
}
