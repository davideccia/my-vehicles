<?php

namespace App\Http\Resources;

use App\Models\VehicleRefuel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VehicleRefuel $resource
 */
class VehicleRefuelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'vehicle_id' => $this->resource->vehicle_id,
            'vehicle' => $this->whenLoaded('vehicle', fn () => (new VehicleResource($this->resource->vehicle))->resolve()),
            'date' => $this->resource->date->format('Y-m-d'),
            'total_price' => $this->resource->total_price,
            'unit_price' => $this->resource->unit_price,
            'liters' => $this->resource->liters,
            'odometer' => $this->resource->odometer,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
