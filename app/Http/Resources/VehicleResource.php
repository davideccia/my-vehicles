<?php

namespace App\Http\Resources;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Vehicle $resource
 */
class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'plate_number' => $this->resource->plate_number,
            'brand' => $this->resource->brand,
            'model' => $this->resource->model,
            'year' => $this->resource->year,
            'purchase_date' => $this->resource->purchase_date?->format('Y-m-d'),
            'full_name' => $this->resource->full_name,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
