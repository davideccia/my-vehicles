<?php

namespace App\Http\Resources;

use App\Models\VehicleServiceReminder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VehicleServiceReminder $resource
 */
class VehicleServiceReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'vehicle_id' => $this->resource->vehicle_id,
            'vehicle_service_type_id' => $this->resource->vehicle_service_type_id,
            'every' => $this->resource->every,
            'vehicle' => $this->whenLoaded('vehicle', fn () => (new VehicleResource($this->resource->vehicle))->resolve()),
            'vehicle_service_type' => $this->whenLoaded('vehicleServiceType', fn () => (new VehicleServiceTypeResource($this->resource->vehicleServiceType))->resolve()),
            'latest_vehicle_service' => $this->resource->vehicle?->latestVehicleService,
            'current_vehicle_odometer' => $this->resource->vehicle?->currentOdometer(),
            'last_vehicle_service_odometer' => $this->resource->latestVehicleServiceOdometer(),
            'overdue_odometer_diff' => $this->resource->overdueOdometerDiff(),
            'is_overdue' => $this->resource->isOverDue(),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
