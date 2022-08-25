<?php

namespace App\Http\Controllers;

use App\Http\Resources\ZipCodeResource;
use App\Models\ZipCode;
use Illuminate\Http\Request;

class ZipCodeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param  ZipCode  $zipCode
     *
     * @return ZipCodeResource
     */
    public function __invoke(Request $request, ZipCode $zipCode): ZipCodeResource
    {
        return new ZipCodeResource($zipCode);
    }
}
