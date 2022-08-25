<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SettlementResource extends JsonResource
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
        return [
            'key' => (int) $this->settlement_key,
            'name' => $this->name,
            'zone_type' => $this->zone_type,
            'settlement_type' => SettlementTypeResource::make($this->settlementType)
        ];
    }
}
