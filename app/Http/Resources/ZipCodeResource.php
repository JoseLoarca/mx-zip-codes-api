<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ZipCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        // We'll use the first settlement to retrieve: municipality, locality and federal entity
        $firstSettlement = $this->settlements[0];
        $municipality = $firstSettlement->municipality;

        return [
            'zip_code' => $this->zip_code,
            'locality' => $firstSettlement->locality->name,
            'federal_entity' => FederalEntityResource::make($municipality->federalEntity),
            'settlements' => SettlementResource::collection($this->settlements),
            'municipality' => MunicipalityResource::make($municipality)
        ];
    }
}
