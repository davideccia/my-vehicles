<?php

namespace App\Http\Resources;

use App\Models\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VehicleService $resource
 */
class VehicleServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'vehicle_id' => $this->resource->vehicle_id,
            'vehicle_service_type_id' => $this->resource->vehicle_service_type_id,
            'vehicle_service_reminder_id' => $this->resource->vehicle_service_reminder_id,
            'vehicle' => $this->whenLoaded('vehicle', fn () => (new VehicleResource($this->resource->vehicle))->resolve()),
            'vehicle_service_type' => $this->whenLoaded('vehicleServiceType', fn () => (new VehicleServiceTypeResource($this->resource->vehicleServiceType))->resolve()),
            'vehicle_service_reminder' => $this->whenLoaded('vehicleServiceReminder', fn () => (new VehicleServiceReminderResource($this->resource->vehicleServiceReminder))->resolve()),
            'date' => $this->resource->date->format('Y-m-d'),
            'total_paid' => $this->resource->total_paid,
            'odometer' => $this->resource->odometer,
            'location' => $this->resource->location,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
